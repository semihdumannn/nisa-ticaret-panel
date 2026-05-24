<?php

namespace App\Modules\Analytics\Application\UseCases;

use App\Models\Order;
use App\Modules\Analytics\Application\DTOs\DateRangeDTO;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetRevenueReportUseCase
{
    /**
     * Return daily revenue grouped by date for the given range.
     * Each item: { date: 'YYYY-MM-DD', revenue: float, orders: int }
     */
    public function execute(DateRangeDTO $range): Collection
    {
        return Order::select(
                DB::raw("DATE(created_at) as date"),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders'),
            )
            ->whereBetween('created_at', [$range->from, $range->to])
            ->whereNotIn('status', [OrderStatus::CANCELLED->value])
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date'    => $row->date,
                'revenue' => round((float) $row->revenue, 2),
                'orders'  => (int) $row->orders,
            ]);
    }
}
