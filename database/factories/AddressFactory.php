<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'title'        => fake()->randomElement(['Home', 'Work', 'Office', 'Other']),
            'full_address' => fake()->address(),
            'district'     => fake()->city(),
            'city'         => fake()->city(),
            'postal_code'  => fake()->postcode(),
            'latitude'     => null,
            'longitude'    => null,
            'is_default'   => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }
}
