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
        'sale_price',
        'unit',
        'min_order_qty',
        'max_order_qty',
        'package_qty',
        'is_koli',
        'stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'attributes'       => 'array',
            'price_adjustment' => 'decimal:2',
            'sale_price'       => 'decimal:2',
            'min_order_qty'    => 'integer',
            'max_order_qty'    => 'integer',
            'package_qty'      => 'integer',
            'is_koli'          => 'boolean',
            'stock'            => 'integer',
            'is_active'        => 'boolean',
        ];
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
