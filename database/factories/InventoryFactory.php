<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventory>
 */
class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(0, 200);

        return [
            'product_id'        => Product::factory(),
            'warehouse_id'      => Warehouse::factory(),
            'variant_id'        => null,
            'quantity'          => $quantity,
            'reserved_quantity' => $this->faker->numberBetween(0, max(0, $quantity - 1)),
            'last_restock_date' => $this->faker->optional(0.7)->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function lowStock(): static
    {
        return $this->state([
            'quantity'          => $this->faker->numberBetween(1, 5),
            'reserved_quantity' => 0,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state([
            'quantity'          => 0,
            'reserved_quantity' => 0,
        ]);
    }

    public function fullyReserved(): static
    {
        return $this->state(function (array $attributes) {
            $qty = $attributes['quantity'] ?? 10;
            return ['reserved_quantity' => $qty];
        });
    }
}
