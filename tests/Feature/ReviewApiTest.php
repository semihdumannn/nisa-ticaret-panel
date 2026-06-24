<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function reviewUser(): User
{
    return User::factory()->create(['role' => 'customer', 'is_active' => true]);
}

function deliveredOrder(User $customer, Product $product): Order
{
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'status'      => OrderStatus::DELIVERED->value,
    ]);
    $order->items()->create([
        'product_id'   => $product->id,
        'product_name' => $product->name,
        'quantity'     => 1,
        'unit_price'   => $product->price,
        'tax_rate'     => 0,
        'total'        => $product->price,
    ]);

    return $order;
}

test('customer can submit a review for delivered order', function () {
    $user    = reviewUser();
    $product = Product::factory()->create();
    $order   = deliveredOrder($user, $product);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/reviews', [
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'rating'     => 5,
            'comment'    => 'Harika!',
            'tags'       => ['Hızlı teslimat'],
        ])
        ->assertCreated()
        ->assertJsonPath('rating', 5);
});

test('cannot review non-delivered order', function () {
    $user    = reviewUser();
    $product = Product::factory()->create();
    $order   = Order::factory()->create(['customer_id' => $user->id, 'status' => OrderStatus::CONFIRMED->value]);
    $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'quantity' => 1, 'unit_price' => 10, 'tax_rate' => 0, 'total' => 10]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/reviews', ['order_id' => $order->id, 'product_id' => $product->id, 'rating' => 4])
        ->assertUnprocessable()
        ->assertJsonPath('error', 'ORDER_NOT_DELIVERED');
});

test('cannot review same product twice', function () {
    $user    = reviewUser();
    $product = Product::factory()->create();
    $order   = deliveredOrder($user, $product);
    Review::create(['user_id' => $user->id, 'order_id' => $order->id, 'product_id' => $product->id, 'rating' => 5]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/reviews', ['order_id' => $order->id, 'product_id' => $product->id, 'rating' => 4])
        ->assertUnprocessable()
        ->assertJsonPath('error', 'ALREADY_REVIEWED');
});

test('can get product reviews', function () {
    $user    = reviewUser();
    $product = Product::factory()->create();
    $order   = deliveredOrder($user, $product);
    Review::create(['user_id' => $user->id, 'order_id' => $order->id, 'product_id' => $product->id, 'rating' => 5, 'comment' => 'İyi']);

    $this->getJson("/api/v1/products/{$product->id}/reviews")
        ->assertOk()
        ->assertJsonStructure(['data', 'summary' => ['average_rating', 'total_reviews', 'distribution'], 'meta']);
});

test('can get order review status', function () {
    $user    = reviewUser();
    $product = Product::factory()->create();
    $order   = deliveredOrder($user, $product);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/orders/{$order->id}/review-status")
        ->assertOk()
        ->assertJsonStructure(['order_id', 'can_review', 'reviewed_product_ids', 'pending_product_ids']);
});
