<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Filament\Widgets\BarChartWidget;

class TopProductsWidget extends BarChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Top 10 Products — This Month';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $rows = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.created_at', '>=', now()->startOfMonth())
            ->whereNotIn('orders.status', [OrderStatus::CANCELLED->value])
            ->selectRaw('products.name, SUM(order_items.quantity) as total_qty')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label'           => 'Units Sold',
                    'data'            => $rows->pluck('total_qty')->all(),
                    'backgroundColor' => 'rgba(231,58,153,0.75)',
                    'borderColor'     => '#E73A99',
                    'borderWidth'     => 1,
                    'borderRadius'    => 4,
                ],
            ],
            'labels' => $rows->pluck('name')->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins'   => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }
}
