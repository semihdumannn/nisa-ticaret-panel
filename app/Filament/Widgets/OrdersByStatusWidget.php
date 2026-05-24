<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Filament\Widgets\DoughnutChartWidget;

class OrdersByStatusWidget extends DoughnutChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Orders by Status';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $counts = Order::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $statuses = OrderStatus::cases();

        return [
            'datasets' => [
                [
                    'label'           => 'Orders',
                    'data'            => collect($statuses)->map(fn ($s) => $counts->get($s->value, 0))->values()->all(),
                    'backgroundColor' => [
                        '#94a3b8', // pending  → gray
                        '#00A6AB', // confirmed → info
                        '#f59e0b', // preparing → amber
                        '#E73A99', // on_the_way → primary
                        '#22c55e', // delivered → green
                        '#ef4444', // cancelled → red
                    ],
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => collect($statuses)->map(fn ($s) => $s->label())->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display'  => true,
                    'position' => 'bottom',
                ],
            ],
            'cutout' => '60%',
        ];
    }
}
