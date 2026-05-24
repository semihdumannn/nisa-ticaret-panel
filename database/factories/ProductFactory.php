<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'brand_id'      => null,
            'sku'           => null, // auto-generated
            'name'          => ucwords($name),
            'slug'          => null, // auto-generated
            'description'   => fake()->paragraph(),
            'barcode'       => null,
            'unit'          => 'piece',
            'price'         => fake()->randomFloat(2, 5, 500),
            'cost_price'    => null,
            'tax_rate'      => 20.00,
            'min_order_qty' => 1,
            'max_order_qty' => null,
            'is_featured'   => false,
            'is_active'     => true,
            'metadata'      => null,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function forBrand(int $brandId): static
    {
        return $this->state(fn () => ['brand_id' => $brandId]);
    }
}
