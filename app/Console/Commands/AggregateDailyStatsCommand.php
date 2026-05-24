<?php

namespace App\Console\Commands;

use App\Modules\Analytics\Application\UseCases\AggregateDailyStatsUseCase;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AggregateDailyStatsCommand extends Command
{
    protected $signature = 'analytics:aggregate-daily
                            {date? : Date to aggregate (YYYY-MM-DD). Defaults to yesterday.}';

    protected $description = 'Compute and upsert daily_stats for a given date (default: yesterday).';

    public function handle(AggregateDailyStatsUseCase $useCase): int
    {
        $dateArg = $this->argument('date');
        $date    = $dateArg ? Carbon::parse($dateArg) : now()->subDay();

        $this->info("Aggregating stats for {$date->toDateString()} …");

        $stat = $useCase->execute($date);

        $this->table(
            ['Date', 'Orders', 'Revenue', 'New Customers', 'Total Customers', 'Avg Order'],
            [[
                $stat->date->toDateString(),
                $stat->total_orders,
                '₺' . number_format((float) $stat->total_revenue, 2),
                $stat->new_customers,
                $stat->total_customers,
                '₺' . number_format((float) $stat->avg_order_value, 2),
            ]],
        );

        $this->info('Done.');
        return self::SUCCESS;
    }
}
