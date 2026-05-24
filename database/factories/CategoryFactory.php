<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'parent_id'   => null,
            'name'        => fake()->unique()->words(2, true),
            'slug'        => null, // auto-generated
            'icon'        => null,
            'color'       => fake()->hexColor(),
            'description' => fake()->sentence(),
            'is_active'   => true,
            'sort_order'  => 0,
        ];
    }

    public function child(Category $parent): static
    {
        return $this->state(fn () => ['parent_id' => $parent->id]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
