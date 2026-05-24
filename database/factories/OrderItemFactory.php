<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $unitPrice = $this->faker->randomFloat(2, 5, 100);
        $qty       = $this->faker->numberBetween(1, 10);
        $taxRate   = 18.0;
        $total     = round($unitPrice * (1 + $taxRate / 100) * $qty, 2);

        return [
            'order_id'        => Order::factory(),
            'product_id'      => Product::factory(),
            'variant_id'      => null,
            'product_name'    => $this->faker->words(3, true),
            'quantity'        => $qty,
            'unit_price'      => $unitPrice,
            'tax_rate'        => $taxRate,
            'discount_amount' => 0,
            'total'           => $total,
        ];
    }
}
