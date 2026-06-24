<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id'       => Product::factory(),
            'sku'              => 'VAR-' . strtoupper(fake()->unique()->lexify('????????')),
            'name'             => fake()->words(2, true),
            'attributes'       => [],
            'price_adjustment' => 0.00,
            'stock'            => fake()->numberBetween(0, 100),
            'is_active'        => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }
}
