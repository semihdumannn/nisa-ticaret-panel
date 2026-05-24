<?php

namespace App\Modules\Analytics\Application\UseCases;

use App\Models\OrderItem;
use App\Modules\Analytics\Application\DTOs\DateRangeDTO;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetTopProductsUseCase
{
    /**
     * Return top products by revenue within the date range.
     * Each item: { product_id, product_name, total_quantity, total_revenue, order_count }
     */
    public function execute(DateRangeDTO $range, int $limit = 10): Collection
    {
        return OrderItem::select(
                'order_items.product_id',
                DB::raw('MAX(order_items.product_name) as product_name'),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_items.order_id) as order_count'),
            )
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$range->from, $range->to])
            ->whereNotIn('orders.status', [OrderStatus::CANCELLED->value])
            ->whereNull('orders.deleted_at')
            ->groupBy('order_items.product_id')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'product_id'    => $row->product_id,
                'product_name'  => $row->product_name,
                'total_quantity' => (int) $row->total_quantity,
                'total_revenue' => round((float) $row->total_revenue, 2),
                'order_count'   => (int) $row->order_count,
            ]);
    }
}
