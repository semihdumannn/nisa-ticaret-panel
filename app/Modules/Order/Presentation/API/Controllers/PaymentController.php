<?php

namespace App\Modules\Order\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Modules\Order\Application\UseCases\UpdateOrderStatusUseCase;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use App\Modules\Order\Domain\ValueObjects\PaymentStatus;
use App\Modules\Order\Infrastructure\External\IyzicoPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly IyzicoPaymentService $iyzico,
        private readonly UpdateOrderStatusUseCase $updateStatus,
    ) {}

    /**
     * POST /api/v1/orders/{order}/pay
     *
     * Initiate iyzico Checkout Form for an order.
     * Returns the payment page URL.
     */
    public function initiate(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        // Only the order owner can initiate payment
        if ($order->customer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($order->payment_status === PaymentStatus::PAID->value) {
            return response()->json(['message' => 'Order is already paid.'], 422);
        }

        // Cannot pay for cancelled or delivered orders
        if (in_array($order->status, [OrderStatus::CANCELLED->value, OrderStatus::DELIVERED->value], strict: true)) {
            return response()->json(['message' => 'Payment is not available for this order.'], 422);
        }

        $callbackUrl = route('api.payment.callback');

        $result = $this->iyzico->initializeCheckout(
            order:       $order->load(['items.product', 'address']),
            customer:    $user,
            callbackUrl: $callbackUrl,
        );

        if (! $result['success']) {
            return response()->json(['message' => $result['message'] ?? 'Payment initialization failed.'], 503);
        }

        return response()->json([
            'checkout_url' => $result['checkout_form_url'],
            'token'        => $result['token'],
        ]);
    }

    /**
     * POST /api/v1/payment/callback
     *
     * iyzico posts here after the checkout form is completed.
     * Verify payment and update order accordingly.
     * NOTE: This endpoint has no auth middleware (iyzico server calls it).
     */
    public function callback(Request $request): JsonResponse
    {
        $token = $request->input('token');

        if (! $token) {
            return response()->json(['message' => 'Missing payment token.'], 422);
        }

        // Verify token with iyzico — conversation_id = order->id we set during init
        $result = $this->iyzico->retrieveCheckoutForm($token);

        $orderId = $result['conversation_id'] ?? null;
        $order   = $orderId ? Order::find($orderId) : null;

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        // ── Idempotency guard ─────────────────────────────────────────────────
        // If already paid, return success without double-processing.
        if ($order->payment_status === PaymentStatus::PAID->value) {
            return response()->json([
                'message'    => 'Payment already recorded.',
                'payment_id' => $order->payment_reference,
            ]);
        }

        if ($result['success']) {
            $order->update([
                'payment_status'    => PaymentStatus::PAID->value,
                'payment_method'    => 'iyzico',
                'payment_reference' => $result['payment_id'],
            ]);

            // Auto-confirm paid orders that are still pending
            if ($order->status === OrderStatus::PENDING->value) {
                try {
                    $this->updateStatus->execute(
                        order:     $order->fresh(),
                        newStatus: OrderStatus::CONFIRMED,
                        note:      'Auto-confirmed after successful iyzico payment. Payment ID: ' . $result['payment_id'],
                    );
                } catch (\Throwable) {
                    // Non-fatal — order is paid, status can be updated manually
                }
            }

            return response()->json([
                'message'    => 'Payment successful.',
                'payment_id' => $result['payment_id'],
            ]);
        }

        // Payment failed — record it but don't overwrite a previous PAID status
        $order->update(['payment_status' => PaymentStatus::FAILED->value]);

        return response()->json([
            'message'    => $result['error_message'] ?? 'Payment failed.',
            'error_code' => $result['error_code'] ?? null,
        ], 402);
    }
}
