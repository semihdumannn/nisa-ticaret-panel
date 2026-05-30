<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'attributes',
        'price_adjustment',
        'stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'attributes'       => 'array',
            'price_adjustment' => 'decimal:2',
            'stock'            => 'integer',
            'is_active'        => 'boolean',
        ];
    }

    // ── Attribute helpers (stored in the attributes JSON column) ──────────────

    public function getSalePriceAttribute(): ?float
    {
        $v = $this->attributes['sale_price'] ?? null;
        return $v !== null ? (float) $v : null;
    }

    public function getPackageQtyAttribute(): int
    {
        return (int) ($this->attributes['package_qty'] ?? 1);
    }

    public function getIsKoliAttribute(): bool
    {
        return (bool) ($this->attributes['is_koli'] ?? false);
    }

    public function getUnitAttribute(): ?string
    {
        return $this->attributes['unit'] ?? null;
    }

    public function getMinOrderQtyAttribute(): ?int
    {
        $v = $this->attributes['min_order_qty'] ?? null;
        return $v !== null ? (int) $v : null;
    }

    public function getMaxOrderQtyAttribute(): ?int
    {
        $v = $this->attributes['max_order_qty'] ?? null;
        return $v !== null ? (int) $v : null;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Effective price including adjustment. */
    public function effectivePrice(): float
    {
        return (float) $this->product->price + (float) $this->price_adjustment;
    }
}
