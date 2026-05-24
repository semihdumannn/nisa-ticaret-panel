<?php

namespace App\Modules\Analytics\Application\UseCases;

use App\Models\Order;
use App\Modules\Analytics\Application\DTOs\DateRangeDTO;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetOrderStatusBreakdownUseCase
{
    /**
     * Return order counts grouped by status for the date range.
     * Each item: { status, label, count, percentage }
     */
    public function execute(?DateRangeDTO $range = null): Collection
    {
        $query = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status');

        if ($range) {
            $query->whereBetween('created_at', [$range->from, $range->to]);
        }

        $rows  = $query->get();
        $total = $rows->sum('count');

        return $rows->map(fn ($row) => [
            'status'     => $row->status,
            'label'      => OrderStatus::from($row->status)->label(),
            'color'      => OrderStatus::from($row->status)->color(),
            'count'      => (int) $row->count,
            'percentage' => $total > 0 ? round((int) $row->count / $total * 100, 1) : 0.0,
        ])->sortByDesc('count')->values();
    }
}
