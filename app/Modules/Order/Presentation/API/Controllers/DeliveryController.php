<?php

namespace App\Modules\Order\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Modules\Order\Application\UseCases\UpdateOrderStatusUseCase;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use App\Modules\Order\Presentation\API\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints for delivery personnel and field agents.
 *
 * Middleware: auth:sanctum + role:delivery,field_agent
 */
class DeliveryController extends Controller
{
    public function __construct(
        private readonly UpdateOrderStatusUseCase $updateStatus,
    ) {}

    /**
     * GET /api/v1/delivery/orders
     *
     * Returns orders assigned to the authenticated delivery user.
     * Field agents see all active (non-terminal) orders.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Order::with(['customer', 'address', 'items.product'])
            ->whereNotIn('status', [OrderStatus::DELIVERED->value, OrderStatus::CANCELLED->value])
            ->orderBy('scheduled_delivery_date')
            ->orderByDesc('created_at');

        // Delivery role: only their assigned orders
        if ($user->role === 'delivery') {
            $query->where('assigned_to', $user->id);
        }

        // Field agents see all active orders (they assign delivery people)
        $orders = $query->paginate(20);

        return response()->json([
            'data'  => OrderResource::collection($orders),
            'meta'  => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/delivery/orders/{order}
     *
     * Show a single order's delivery details.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        // Delivery: only their own assigned orders
        if ($user->role === 'delivery' && $order->assigned_to !== $user->id) {
            return response()->json(['message' => 'Order not assigned to you.'], 403);
        }

        $order->load(['customer', 'address', 'items.product', 'items.variant', 'statusHistory']);

        return response()->json(['order' => new OrderResource($order)]);
    }

    /**
     * PUT /api/v1/delivery/orders/{order}/on-the-way
     *
     * Mark order as ON_THE_WAY (delivery started).
     */
    public function markOnTheWay(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'delivery' && $order->assigned_to !== $user->id) {
            return response()->json(['message' => 'Order not assigned to you.'], 403);
        }

        try {
            $updated = $this->updateStatus->execute(
                order:     $order,
                newStatus: OrderStatus::ON_THE_WAY,
                note:      'Delivery started by ' . $user->name,
                userId:    $user->id,
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Order marked as on the way.',
            'status'  => $updated->status,
        ]);
    }

    /**
     * PUT /api/v1/delivery/orders/{order}/deliver
     *
     * Mark order as DELIVERED.
     * Sets delivered_at timestamp automatically (handled in UpdateOrderStatusUseCase).
     */
    public function markDelivered(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'delivery' && $order->assigned_to !== $user->id) {
            return response()->json(['message' => 'Order not assigned to you.'], 403);
        }

        $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $updated = $this->updateStatus->execute(
                order:     $order,
                newStatus: OrderStatus::DELIVERED,
                note:      $request->input('note') ?? 'Delivered by ' . $user->name,
                userId:    $user->id,
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message'      => 'Order marked as delivered.',
            'status'       => $updated->status,
            'delivered_at' => $updated->delivered_at?->toIso8601String(),
        ]);
    }

    /**
     * PUT /api/v1/delivery/orders/{order}/assign
     *
     * Assign an order to a delivery user. Field agent only.
     */
    public function assign(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $deliveryUser = \App\Models\User::where('id', $request->user_id)
            ->where('role', 'delivery')
            ->where('is_active', true)
            ->first();

        if (! $deliveryUser) {
            return response()->json(['message' => 'Invalid delivery user.'], 422);
        }

        $order->update(['assigned_to' => $deliveryUser->id]);

        return response()->json([
            'message'      => "Order assigned to {$deliveryUser->name}.",
            'assigned_to'  => $deliveryUser->id,
        ]);
    }
}
