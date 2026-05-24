<?php

namespace App\Filament\Widgets;

use App\Modules\Analytics\Application\DTOs\DateRangeDTO;
use App\Modules\Analytics\Application\UseCases\GetRevenueReportUseCase;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Revenue — Last 30 Days';

    protected int | string | array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $range = DateRangeDTO::lastDays(30);
        $rows  = app(GetRevenueReportUseCase::class)->execute($range);

        $labels  = $rows->pluck('date')->all();
        $revenue = $rows->pluck('revenue')->all();

        return [
            'datasets' => [
                [
                    'label'           => 'Revenue (₺)',
                    'data'            => $revenue,
                    'borderColor'     => '#E73A99',
                    'backgroundColor' => 'rgba(231,58,153,0.08)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
