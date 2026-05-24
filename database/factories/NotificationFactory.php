<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use App\Modules\Notification\Domain\ValueObjects\NotificationType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type'    => NotificationType::ORDER_UPDATE->value,
            'title'   => $this->faker->sentence(4),
            'body'    => $this->faker->sentence(),
            'data'    => null,
            'is_read' => false,
            'read_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function unread(): static
    {
        return $this->state([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    public function ofType(NotificationType $type): static
    {
        return $this->state(['type' => $type->value]);
    }
}
