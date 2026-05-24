<?php

namespace App\Modules\Analytics\Application\UseCases;

use App\Models\Order;
use App\Modules\Analytics\Application\DTOs\DateRangeDTO;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetTopCustomersUseCase
{
    /**
     * Return top customers by total spend within the date range.
     * Each item: { customer_id, customer_name, order_count, total_spend }
     */
    public function execute(DateRangeDTO $range, int $limit = 10): Collection
    {
        return Order::select(
                'orders.customer_id',
                DB::raw('MAX(users.name) as customer_name'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(orders.total) as total_spend'),
            )
            ->join('users', 'users.id', '=', 'orders.customer_id')
            ->whereBetween('orders.created_at', [$range->from, $range->to])
            ->whereNotIn('orders.status', [OrderStatus::CANCELLED->value])
            ->whereNotNull('orders.customer_id')
            ->groupBy('orders.customer_id')
            ->orderByDesc('total_spend')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'customer_id'   => $row->customer_id,
                'customer_name' => $row->customer_name,
                'order_count'   => (int) $row->order_count,
                'total_spend'   => round((float) $row->total_spend, 2),
            ]);
    }
}
