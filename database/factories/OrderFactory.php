<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use App\Modules\Order\Domain\ValueObjects\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        static $seq = 1;

        $subtotal = $this->faker->randomFloat(2, 50, 500);
        $tax      = round($subtotal * 0.18, 2);
        $total    = round($subtotal + $tax, 2);

        return [
            'order_number'    => 'ORD-' . now()->format('Ymd') . '-' . str_pad($seq++, 5, '0', STR_PAD_LEFT),
            'customer_id'     => User::factory(),
            'address_id'      => null,
            'status'          => OrderStatus::PENDING->value,
            'subtotal'        => $subtotal,
            'discount_amount' => 0,
            'tax_amount'      => $tax,
            'shipping_amount' => 0,
            'total'           => $total,
            'payment_method'  => $this->faker->randomElement(['cash', 'credit_card', 'account']),
            'payment_status'  => PaymentStatus::PENDING->value,
            'notes'           => null,
            'internal_notes'  => null,
            'assigned_to'     => null,
            'created_by'      => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(['status' => OrderStatus::CONFIRMED->value]);
    }

    public function delivered(): static
    {
        return $this->state([
            'status'       => OrderStatus::DELIVERED->value,
            'delivered_at' => now(),
            'payment_status' => PaymentStatus::PAID->value,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => OrderStatus::CANCELLED->value]);
    }
}
