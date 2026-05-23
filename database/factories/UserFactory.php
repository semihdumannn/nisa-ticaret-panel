<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'firebase_uid'      => null,
            'name'              => fake()->name(),
            'phone'             => '+90555' . fake()->unique()->numerify('#######'),
            'email'             => fake()->unique()->safeEmail(),
            'role'              => 'customer',
            'is_active'         => true,
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'remember_token'    => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin']);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function withFirebaseUid(): static
    {
        return $this->state(fn () => ['firebase_uid' => 'firebase-' . Str::random(20)]);
    }
}
