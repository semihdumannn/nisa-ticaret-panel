<?php

namespace App\Modules\Analytics\Application\DTOs;

readonly class DashboardStatsDTO
{
    public function __construct(
        // Today
        public int   $todayOrders,
        public float $todayRevenue,
        public int   $todayNewCustomers,

        // Month-to-date
        public int   $monthOrders,
        public float $monthRevenue,

        // All-time
        public int   $totalCustomers,
        public int   $totalOrders,
        public float $totalRevenue,

        // Pending / active
        public int   $pendingOrders,
        public int   $lowStockProducts,
    ) {}
}
