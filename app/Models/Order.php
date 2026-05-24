<?php

namespace App\Models;

use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use App\Modules\Order\Domain\ValueObjects\PaymentStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_id',
        'address_id',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'shipping_amount',
        'total',
        'payment_method',
        'payment_status',
        'notes',
        'internal_notes',
        'assigned_to',
        'scheduled_delivery_date',
        'delivered_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'                => 'decimal:2',
            'discount_amount'         => 'decimal:2',
            'tax_amount'              => 'decimal:2',
            'shipping_amount'         => 'decimal:2',
            'total'                   => 'decimal:2',
            'scheduled_delivery_date' => 'date',
            'delivered_at'            => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Status helpers ────────────────────────────────────────────────────────

    public function orderStatus(): OrderStatus
    {
        return OrderStatus::from($this->status);
    }

    public function paymentStatus(): PaymentStatus
    {
        return PaymentStatus::from($this->payment_status);
    }

    public function isCancelled(): bool
    {
        return $this->status === OrderStatus::CANCELLED->value;
    }

    public function isDelivered(): bool
    {
        return $this->status === OrderStatus::DELIVERED->value;
    }

    public function canTransitionTo(OrderStatus $next): bool
    {
        return $this->orderStatus()->canTransitionTo($next);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            OrderStatus::DELIVERED->value,
            OrderStatus::CANCELLED->value,
        ]);
    }
}
