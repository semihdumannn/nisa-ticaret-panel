<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = [
        'product_id',
        'variant_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'last_restock_date',
    ];

    protected function casts(): array
    {
        return [
            'quantity'          => 'integer',
            'reserved_quantity' => 'integer',
            'last_restock_date' => 'datetime',
        ];
    }

    // ── Computed ──────────────────────────────────────────────────────────────

    /** Quantity physically available to allocate. */
    public function availableQuantity(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    /** True when stock is critically low (threshold can be configured). */
    public function isLowStock(int $threshold = 5): bool
    {
        return $this->availableQuantity() <= $threshold;
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeLowStock($query, int $threshold = 5)
    {
        return $query->whereRaw('(quantity - reserved_quantity) <= ?', [$threshold])
            ->where('quantity', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->whereRaw('(quantity - reserved_quantity) <= 0');
    }
}
