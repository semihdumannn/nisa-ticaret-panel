<?php

namespace App\Modules\Order\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Modules\Inventory\Domain\Exceptions\InsufficientStockException;
use App\Modules\Order\Application\UseCases\CreateFieldAgentOrderUseCase;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use App\Modules\Order\Presentation\API\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FieldAgentController extends Controller
{
    public function __construct(
        private readonly CreateFieldAgentOrderUseCase $createOrder,
    ) {}

    /**
     * GET /api/v1/field-agent/stats
     * Bugünkü istatistikler (agent'a göre).
     */
    public function stats(Request $request): JsonResponse
    {
        $agentId   = $request->user()->id;
        $today     = now()->toDateString();
        $cancelled = OrderStatus::CANCELLED->value;

        $row = DB::table('orders')
            ->whereNull('deleted_at')
            ->where('created_by', $agentId)
            ->whereRaw('"created_at"::date = ?', [$today])
            ->selectRaw(implode(', ', [
                'COUNT(*) as total_orders',
                "SUM(CASE WHEN status = 'pending'    THEN 1 ELSE 0 END) as pending_orders",
                "SUM(CASE WHEN status = 'preparing'  THEN 1 ELSE 0 END) as preparing_orders",
                "SUM(CASE WHEN status = 'on_the_way' THEN 1 ELSE 0 END) as on_the_way_orders",
                "SUM(CASE WHEN status = 'delivered'  THEN 1 ELSE 0 END) as delivered_orders",
                "COALESCE(SUM(CASE WHEN status != '$cancelled' THEN total::numeric ELSE 0 END), 0) as total_sales",
            ]))
            ->first();

        return response()->json([
            'total_orders'      => (int) $row->total_orders,
            'pending_orders'    => (int) $row->pending_orders,
            'preparing_orders'  => (int) $row->preparing_orders,
            'on_the_way_orders' => (int) $row->on_the_way_orders,
            'delivered_orders'  => (int) $row->delivered_orders,
            'total_sales'       => round((float) $row->total_sales, 2),
            'date'              => $today,
        ]);
    }

    /**
     * GET /api/v1/field-agent/today-orders
     * Bugün oluşturulan siparişler.
     */
    public function todayOrders(Request $request): JsonResponse
    {
        $orders = Order::with(['customer', 'address', 'items.product'])
            ->where('created_by', $request->user()->id)
            ->whereDate('created_at', now()->toDateString())
            ->orderByDesc('created_at')
            ->get();

        $data = $orders->map(fn (Order $o) => [
            'id'               => $o->id,
            'order_number'     => $o->order_number,
            'status'           => $o->status,
            'customer_name'    => $o->customer?->name,
            'customer_phone'   => $o->customer?->phone,
            'total'            => (float) $o->total,
            'items'            => $o->items->map(fn ($i) => [
                'product_name' => $i->product_name,
                'quantity'     => $i->quantity,
                'unit_price'   => (float) $i->unit_price,
            ]),
            'address'          => $o->address?->full_address,
            'created_at'       => $o->created_at->toIso8601String(),
        ]);

        return response()->json(['data' => $data]);
    }

    /**
     * POST /api/v1/field-agent/orders
     * Hızlı sipariş oluştur (cart'sız).
     */
    public function store(Request $request): JsonResponse
    {
        $v = $request->validate([
            'customer_id'    => ['nullable', 'integer', 'exists:users,id'],
            'customer_name'  => ['nullable', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
            'address_id'     => ['nullable', 'integer', 'exists:addresses,id'],
            'address'        => ['nullable', 'string', 'max:500'],
            'payment_method' => ['nullable', 'string', 'in:cash,card_on_delivery'],
            'notes'          => ['nullable', 'string', 'max:1000'],
            'time_slot'      => ['nullable', 'string', 'max:50'],
        ]);

        if (empty($v['customer_id']) && empty($v['customer_phone'])) {
            return response()->json(['message' => 'customer_id or customer_phone is required.'], 422);
        }

        try {
            $order = $this->createOrder->execute([
                'agent_id'       => $request->user()->id,
                'customer_id'    => $v['customer_id'] ?? null,
                'customer_name'  => $v['customer_name'] ?? null,
                'customer_phone' => $v['customer_phone'] ?? null,
                'items'          => $v['items'],
                'address_id'     => $v['address_id'] ?? null,
                'address_text'   => $v['address'] ?? null,
                'payment_method' => $v['payment_method'] ?? 'cash',
                'notes'          => $v['notes'] ?? null,
                'time_slot'      => $v['time_slot'] ?? null,
            ]);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'message'   => $e->getMessage(),
                'requested' => $e->requested,
                'available' => $e->available,
            ], 422);
        }

        return response()->json(['data' => new OrderResource($order)], 201);
    }
}
