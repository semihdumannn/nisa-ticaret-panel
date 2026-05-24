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

        $callbackUrl = route('api.payment.callback', ['order' => $order->id]);

        $result = $this->iyzico->initializeCheckout(
            order:       $order->load(['items.product', 'address']),
            customer:    $user,
            callbackUrl: $callbackUrl,
        );

        if (! $result['success']) {
            return response()->json(['message' => $result['message']], 503);
        }

        return response()->json([
            'checkout_url' => $result['checkout_form_url'],
            'token'        => $result['token'],
        ]);
    }

    /**
     * POST /api/v1/payment/callback
     *
     * iyzico redirects here after the checkout form is completed.
     * Verify payment and update order accordingly.
     */
    public function callback(Request $request): JsonResponse
    {
        $token   = $request->input('token');
        $orderId = $request->input('conversationId') ?? $request->route('order');

        if (! $token) {
            return response()->json(['message' => 'Missing payment token.'], 422);
        }

        $result = $this->iyzico->retrieveCheckoutForm($token);

        $order = Order::find($orderId);

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if ($result['success']) {
            $order->update([
                'payment_status' => PaymentStatus::PAID->value,
                'payment_method' => 'iyzico',
            ]);

            // Auto-confirm paid orders
            if ($order->status === OrderStatus::PENDING->value) {
                try {
                    $this->updateStatus->execute(
                        order:     $order,
                        newStatus: OrderStatus::CONFIRMED,
                        note:      'Auto-confirmed after successful iyzico payment. Payment ID: ' . $result['payment_id'],
                    );
                } catch (\Throwable) {
                    // Non-fatal — order is paid, status update can be done manually
                }
            }

            return response()->json([
                'message'    => 'Payment successful.',
                'payment_id' => $result['payment_id'],
            ]);
        }

        // Payment failed
        $order->update(['payment_status' => PaymentStatus::FAILED->value]);

        return response()->json([
            'message'    => $result['error_message'] ?? 'Payment failed.',
            'error_code' => $result['error_code'],
        ], 402);
    }
}
