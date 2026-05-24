<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Modules\Campaign\Domain\ValueObjects\CampaignType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Campaign>
 */
class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        return [
            'name'                => $this->faker->words(3, true) . ' Sale',
            'description'         => $this->faker->sentence(),
            'type'                => CampaignType::PERCENTAGE->value,
            'value'               => $this->faker->randomFloat(2, 5, 30),
            'min_purchase_amount' => null,
            'max_discount_amount' => null,
            'start_date'          => now()->subDay(),
            'end_date'            => now()->addMonth(),
            'is_active'           => true,
            'usage_limit'         => null,
            'usage_count'         => 0,
        ];
    }

    /** Active percentage-off campaign. */
    public function percentage(float $value = 10.0): static
    {
        return $this->state([
            'type'  => CampaignType::PERCENTAGE->value,
            'value' => $value,
        ]);
    }

    /** Active fixed-amount campaign. */
    public function fixedAmount(float $value = 20.0): static
    {
        return $this->state([
            'type'  => CampaignType::FIXED_AMOUNT->value,
            'value' => $value,
        ]);
    }

    /** Inactive campaign. */
    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    /** Campaign that has not started yet. */
    public function upcoming(): static
    {
        return $this->state([
            'start_date' => now()->addWeek(),
            'end_date'   => now()->addMonths(2),
        ]);
    }

    /** Campaign that has already ended. */
    public function expired(): static
    {
        return $this->state([
            'start_date' => now()->subMonth(),
            'end_date'   => now()->subDay(),
        ]);
    }

    /** Campaign with usage limit reached. */
    public function usageLimitReached(): static
    {
        return $this->state([
            'usage_limit' => 10,
            'usage_count' => 10,
        ]);
    }
}
