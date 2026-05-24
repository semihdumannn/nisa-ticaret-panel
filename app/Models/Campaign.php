<?php

namespace App\Models;

use App\Modules\Campaign\Domain\ValueObjects\CampaignType;
use Database\Factories\CampaignFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Campaign extends Model
{
    /** @use HasFactory<CampaignFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'value',
        'min_purchase_amount',
        'max_discount_amount',
        'start_date',
        'end_date',
        'is_active',
        'usage_limit',
        'usage_count',
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
            'usage_limit'         => 'integer',
            'usage_count'         => 'integer',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'campaign_products')
            ->withTimestamps();
    }

    // ── Computed ──────────────────────────────────────────────────────────────

    public function campaignType(): CampaignType
    {
        return CampaignType::from($this->type);
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
     * Returns 0 for BUY_X_GET_Y (handled separately per order item).
     */
    public function calculateDiscount(float $subtotal): float
    {
        if (! $this->isCurrentlyActive()) {
            return 0.0;
        }

        if ($this->min_purchase_amount && $subtotal < (float) $this->min_purchase_amount) {
            return 0.0;
        }

        $discount = match ($this->campaignType()) {
            CampaignType::PERCENTAGE   => $subtotal * ((float) $this->value / 100),
            CampaignType::FIXED_AMOUNT => (float) $this->value,
            CampaignType::BUY_X_GET_Y  => 0.0,  // handled at item level
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

    public function scopeForProduct($query, int $productId)
    {
        return $query->whereHas('products', fn ($q) => $q->where('product_id', $productId));
    }
}
