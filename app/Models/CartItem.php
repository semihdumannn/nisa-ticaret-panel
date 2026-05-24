<?php

namespace App\Models;

use Database\Factories\CartItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    /** @use HasFactory<CartItemFactory> */
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return ['quantity' => 'integer'];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    // ── Computed ──────────────────────────────────────────────────────────────

    /** Effective unit price (variant adjustment applied if present). */
    public function unitPrice(): float
    {
        if ($this->variant) {
            return (float) $this->variant->effectivePrice();
        }

        return (float) ($this->product?->price ?? 0);
    }

    /** Line total before tax. */
    public function lineTotal(): float
    {
        return $this->unitPrice() * $this->quantity;
    }
}
