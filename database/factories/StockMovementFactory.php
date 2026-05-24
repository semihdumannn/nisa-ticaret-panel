<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Modules\Inventory\Domain\ValueObjects\MovementType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(MovementType::cases());

        return [
            'product_id'     => Product::factory(),
            'warehouse_id'   => Warehouse::factory(),
            'variant_id'     => null,
            'type'           => $type->value,
            'quantity'       => $this->faker->numberBetween(1, 100),
            'reason'         => $this->faker->sentence(5),
            'reference_type' => null,
            'reference_id'   => null,
            'user_id'        => null,
        ];
    }

    public function in(): static
    {
        return $this->state(['type' => MovementType::IN->value, 'quantity' => $this->faker->numberBetween(1, 100)]);
    }

    public function out(): static
    {
        return $this->state(['type' => MovementType::OUT->value, 'quantity' => $this->faker->numberBetween(1, 50)]);
    }

    public function transfer(): static
    {
        return $this->state(['type' => MovementType::TRANSFER->value]);
    }

    public function adjustment(): static
    {
        return $this->state(['type' => MovementType::ADJUSTMENT->value]);
    }
}
