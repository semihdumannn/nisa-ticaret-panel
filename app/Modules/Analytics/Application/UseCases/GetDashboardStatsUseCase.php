<?php

namespace App\Modules\Analytics\Application\UseCases;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\User;
use App\Modules\Analytics\Application\DTOs\DashboardStatsDTO;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Illuminate\Support\Facades\DB;

class GetDashboardStatsUseCase
{
    public function execute(): DashboardStatsDTO
    {
        $todayStart  = now()->startOfDay();
        $todayEnd    = now()->endOfDay();
        $monthStart  = now()->startOfMonth();

        // ── Today ─────────────────────────────────────────────────────────────

        $todayOrders = Order::whereBetween('created_at', [$todayStart, $todayEnd])
            ->whereNotIn('status', [OrderStatus::CANCELLED->value])
            ->count();

        $todayRevenue = (float) Order::whereBetween('created_at', [$todayStart, $todayEnd])
            ->whereNotIn('status', [OrderStatus::CANCELLED->value])
            ->sum('total');

        $todayNewCustomers = User::whereBetween('created_at', [$todayStart, $todayEnd])->count();

        // ── Month-to-date ─────────────────────────────────────────────────────

        $monthOrders = Order::where('created_at', '>=', $monthStart)
            ->whereNotIn('status', [OrderStatus::CANCELLED->value])
            ->count();

        $monthRevenue = (float) Order::where('created_at', '>=', $monthStart)
            ->whereNotIn('status', [OrderStatus::CANCELLED->value])
            ->sum('total');

        // ── All-time ──────────────────────────────────────────────────────────

        $totalCustomers = User::count();
        $totalOrders    = Order::whereNotIn('status', [OrderStatus::CANCELLED->value])->count();
        $totalRevenue   = (float) Order::whereNotIn('status', [OrderStatus::CANCELLED->value])->sum('total');

        // ── Active state ──────────────────────────────────────────────────────

        $pendingOrders = Order::where('status', OrderStatus::PENDING->value)->count();

        // Products where available stock ≤ 5 (uses the same 5-unit default threshold as Inventory::isLowStock())
        $lowStockProducts = Inventory::whereRaw('(quantity - reserved_quantity) <= 5')
            ->where('quantity', '>', 0)
            ->distinct('product_id')
            ->count('product_id');

        return new DashboardStatsDTO(
            todayOrders:      $todayOrders,
            todayRevenue:     round($todayRevenue, 2),
            todayNewCustomers: $todayNewCustomers,
            monthOrders:      $monthOrders,
            monthRevenue:     round($monthRevenue, 2),
            totalCustomers:   $totalCustomers,
            totalOrders:      $totalOrders,
            totalRevenue:     round($totalRevenue, 2),
            pendingOrders:    $pendingOrders,
            lowStockProducts: $lowStockProducts,
        );
    }
}
