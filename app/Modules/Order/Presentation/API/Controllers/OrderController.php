<?php

namespace App\Modules\Order\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Modules\Inventory\Domain\Exceptions\InsufficientStockException;
use App\Modules\Order\Application\DTOs\CreateOrderDTO;
use App\Modules\Order\Application\UseCases\CancelOrderUseCase;
use App\Modules\Order\Application\UseCases\CreateOrderUseCase;
use App\Modules\Order\Application\UseCases\UpdateOrderStatusUseCase;
use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use App\Modules\Order\Domain\Exceptions\EmptyCartException;
use App\Modules\Order\Domain\Exceptions\InvalidOrderTransitionException;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use App\Modules\Order\Presentation\API\Requests\CreateOrderRequest;
use App\Modules\Order\Presentation\API\Requests\UpdateOrderStatusRequest;
use App\Modules\Order\Presentation\API\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly OrderRepositoryInterface $orderRepo) {}

    /**
     * GET /api/v1/orders
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderRepo->forCustomer($request->user()->id);
        return response()->json(OrderResource::collection($orders)->response()->getData(true));
    }

    /**
     * GET /api/v1/orders/{order}
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->customer_id !== $request->user()->id && ! $request->user()->hasRole('admin')) {
            abort(403);
        }

        return response()->json(['data' => new OrderResource($order->load('items', 'address'))]);
    }

    /**
     * POST /api/v1/orders
     */
    public function store(CreateOrderRequest $request, CreateOrderUseCase $useCase): JsonResponse
    {
        $v = $request->validated();

        try {
            $order = $useCase->execute(new CreateOrderDTO(
                userId:        $request->user()->id,
                addressId:     $v['address_id'],
                paymentMethod: $v['payment_method'] ?? null,
                notes:         $v['notes'] ?? null,
            ));
        } catch (EmptyCartException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'message'   => $e->getMessage(),
                'requested' => $e->requested,
                'available' => $e->available,
            ], 422);
        }

        return response()->json(['data' => new OrderResource($order)], 201);
    }

    /**
     * POST /api/v1/orders/{order}/cancel
     */
    public function cancel(Request $request, Order $order, CancelOrderUseCase $useCase): JsonResponse
    {
        if ($order->customer_id !== $request->user()->id && ! $request->user()->hasRole('admin')) {
            abort(403);
        }

        try {
            $updated = $useCase->execute(
                $order,
                $request->input('reason'),
                $request->user()->id,
            );
        } catch (InvalidOrderTransitionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => new OrderResource($updated)]);
    }

    // ── Admin endpoints ───────────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/orders
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $orders = $this->orderRepo->paginate(20, $request->only(['status', 'customer_id']));
        return response()->json(OrderResource::collection($orders)->response()->getData(true));
    }

    /**
     * PUT /api/v1/admin/orders/{order}/status
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order, UpdateOrderStatusUseCase $useCase): JsonResponse
    {
        $v = $request->validated();

        try {
            $updated = $useCase->execute(
                $order,
                OrderStatus::from($v['status']),
                $v['note'] ?? null,
                $request->user()->id,
            );
        } catch (InvalidOrderTransitionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => new OrderResource($updated)]);
    }
}
