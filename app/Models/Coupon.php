<?php

namespace App\Models;

use App\Modules\Campaign\Domain\ValueObjects\CouponType;
use Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_purchase_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_count',
        'user_specific',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value'               => 'decimal:2',
            'min_purchase_amount' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'start_date'          => 'datetime',
            'end_date'            => 'datetime',
            'is_active'           => 'boolean',
            'user_specific'       => 'boolean',
            'usage_limit'         => 'integer',
            'usage_count'         => 'integer',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    // ── Computed ──────────────────────────────────────────────────────────────

    public function couponType(): CouponType
    {
        return CouponType::from($this->type);
    }

    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();
        return $now->between($this->start_date, $this->end_date);
    }

    public function isUsageLimitReached(): bool
    {
        return $this->usage_limit !== null && $this->usage_count >= $this->usage_limit;
    }

    /**
     * Calculate the discount for a given subtotal.
     */
    public function calculateDiscount(float $subtotal): float
    {
        $discount = match ($this->couponType()) {
            CouponType::PERCENTAGE   => $subtotal * ((float) $this->value / 100),
            CouponType::FIXED_AMOUNT => (float) $this->value,
        };

        if ($this->max_discount_amount) {
            $discount = min($discount, (float) $this->max_discount_amount);
        }

        return round(min($discount, $subtotal), 2);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }
}
