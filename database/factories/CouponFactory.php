<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Modules\Campaign\Domain\ValueObjects\CouponType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    private static int $counter = 0;

    public function definition(): array
    {
        self::$counter++;
        return [
            'code'                => 'TEST' . strtoupper($this->faker->lexify('????')) . self::$counter,
            'type'                => CouponType::PERCENTAGE->value,
            'value'               => $this->faker->randomFloat(2, 5, 30),
            'min_purchase_amount' => null,
            'max_discount_amount' => null,
            'usage_limit'         => null,
            'usage_count'         => 0,
            'user_specific'       => false,
            'start_date'          => now()->subDay(),
            'end_date'            => now()->addMonth(),
            'is_active'           => true,
        ];
    }

    /** Active percentage coupon with a known code. */
    public function percentage(float $value = 10.0, string $code = 'PERCENT10'): static
    {
        return $this->state([
            'code'  => $code,
            'type'  => CouponType::PERCENTAGE->value,
            'value' => $value,
        ]);
    }

    /** Active fixed-amount coupon with a known code. */
    public function fixedAmount(float $value = 20.0, string $code = 'FIXED20'): static
    {
        return $this->state([
            'code'  => $code,
            'type'  => CouponType::FIXED_AMOUNT->value,
            'value' => $value,
        ]);
    }

    /** Inactive coupon. */
    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    /** Expired coupon. */
    public function expired(): static
    {
        return $this->state([
            'start_date' => now()->subMonth(),
            'end_date'   => now()->subDay(),
        ]);
    }

    /** Usage limit reached. */
    public function usageLimitReached(): static
    {
        return $this->state([
            'usage_limit' => 5,
            'usage_count' => 5,
        ]);
    }

    /** Single-use per user. */
    public function userSpecific(): static
    {
        return $this->state(['user_specific' => true]);
    }

    /** Requires minimum purchase. */
    public function withMinPurchase(float $amount): static
    {
        return $this->state(['min_purchase_amount' => $amount]);
    }
}
