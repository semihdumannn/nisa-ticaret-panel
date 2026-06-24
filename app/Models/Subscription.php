<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id', 'product_id', 'variant_id', 'quantity', 'address_id',
        'plan', 'discount_rate', 'status', 'next_order_date', 'last_order_id',
        'start_date', 'pause_until', 'cancelled_at', 'notes',
    ];

    protected $casts = [
        'next_order_date' => 'date',
        'start_date'      => 'date',
        'pause_until'     => 'date',
        'cancelled_at'    => 'datetime',
        'discount_rate'   => 'float',
        'quantity'        => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function lastOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'last_order_id');
    }
}
