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

    private function jsonAttrs(): array
    {
        return $this->getAttribute('attributes') ?? [];
    }

    public function getSalePriceAttribute(): ?float
    {
        $v = $this->jsonAttrs()['sale_price'] ?? null;
        return $v !== null ? (float) $v : null;
    }

    public function getPackageQtyAttribute(): int
    {
        return (int) ($this->jsonAttrs()['package_qty'] ?? 1);
    }

    public function getIsKoliAttribute(): bool
    {
        return (bool) ($this->jsonAttrs()['is_koli'] ?? false);
    }

    public function getUnitAttribute(): ?string
    {
        return $this->jsonAttrs()['unit'] ?? null;
    }

    public function getMinOrderQtyAttribute(): ?int
    {
        $v = $this->jsonAttrs()['min_order_qty'] ?? null;
        return $v !== null ? (int) $v : null;
    }

    public function getMaxOrderQtyAttribute(): ?int
    {
        $v = $this->jsonAttrs()['max_order_qty'] ?? null;
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
