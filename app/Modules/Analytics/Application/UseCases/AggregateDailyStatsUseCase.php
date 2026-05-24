<?php

namespace App\Modules\Analytics\Application\UseCases;

use App\Models\DailyStat;
use App\Models\Order;
use App\Models\User;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AggregateDailyStatsUseCase
{
    /**
     * Compute and upsert daily_stats for the given date.
     * Safe to run multiple times for the same date (idempotent).
     */
    public function execute(Carbon $date): DailyStat
    {
        $start = $date->copy()->startOfDay();
        $end   = $date->copy()->endOfDay();

        $orders = Order::whereBetween('created_at', [$start, $end])
            ->whereNotIn('status', [OrderStatus::CANCELLED->value])
            ->selectRaw('COUNT(*) as cnt, SUM(total) as rev')
            ->first();

        $totalOrders  = (int) ($orders->cnt ?? 0);
        $totalRevenue = round((float) ($orders->rev ?? 0), 2);
        $avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0.0;

        $totalCustomers = User::where('created_at', '<=', $end)->count();
        $newCustomers   = User::whereBetween('created_at', [$start, $end])->count();

        // Explicit find + update/create avoids issues with Eloquent date-cast
        // transforming the WHERE condition in updateOrCreate.
        $dateStr = $date->toDateString();
        $data    = [
            'total_orders'    => $totalOrders,
            'total_revenue'   => $totalRevenue,
            'total_customers' => $totalCustomers,
            'new_customers'   => $newCustomers,
            'avg_order_value' => $avgOrderValue,
        ];

        // Use whereDate() so SQLite's DATE() function normalises datetime columns
        // (the 'date' Eloquent cast stores as 'Y-m-d H:i:s' in SQLite).
        $stat = DailyStat::whereDate('date', $dateStr)->first();

        if ($stat) {
            $stat->update($data);
            return $stat->fresh();
        }

        return DailyStat::create(array_merge(['date' => $dateStr], $data));
    }
}
