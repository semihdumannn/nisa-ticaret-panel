<?php

namespace App\Filament\Widgets;

use App\Modules\Analytics\Application\UseCases\GetDashboardStatsUseCase;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $stats = app(GetDashboardStatsUseCase::class)->execute();

        return [
            Stat::make("Today's Orders", $stats->todayOrders)
                ->description('Non-cancelled orders placed today')
                ->color('primary')
                ->icon(Heroicon::OutlinedShoppingBag),

            Stat::make("Today's Revenue", '₺' . number_format($stats->todayRevenue, 2))
                ->description('Revenue from today\'s orders')
                ->color('success')
                ->icon(Heroicon::OutlinedCurrencyDollar),

            Stat::make('Pending Orders', $stats->pendingOrders)
                ->description('Awaiting confirmation')
                ->color($stats->pendingOrders > 0 ? 'warning' : 'gray')
                ->icon(Heroicon::OutlinedClock),

            Stat::make('Month Orders', $stats->monthOrders)
                ->description('₺' . number_format($stats->monthRevenue, 2) . ' MTD revenue')
                ->color('info')
                ->icon(Heroicon::OutlinedCalendar),

            Stat::make('Total Customers', number_format($stats->totalCustomers))
                ->description($stats->todayNewCustomers . ' new today')
                ->color('gray')
                ->icon(Heroicon::OutlinedUsers),

            Stat::make('Low Stock Products', $stats->lowStockProducts)
                ->description('Products at or below minimum stock')
                ->color($stats->lowStockProducts > 0 ? 'danger' : 'success')
                ->icon(Heroicon::OutlinedExclamationCircle),
        ];
    }
}
