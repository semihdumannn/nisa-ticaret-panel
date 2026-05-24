<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyStat extends Model
{
    protected $table = 'daily_stats';

    protected $fillable = [
        'date',
        'total_orders',
        'total_revenue',
        'total_customers',
        'new_customers',
        'avg_order_value',
    ];

    protected function casts(): array
    {
        return [
            'date'            => 'date',
            'total_orders'    => 'integer',
            'total_revenue'   => 'decimal:2',
            'total_customers' => 'integer',
            'new_customers'   => 'integer',
            'avg_order_value' => 'decimal:2',
        ];
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeInRange($query, string $from, string $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }
}
