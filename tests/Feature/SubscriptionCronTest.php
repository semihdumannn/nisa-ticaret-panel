<?php

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function cronUser(): User
{
    return User::factory()->create(['role' => 'customer', 'is_active' => true]);
}

function dueSubscription(User $user, ?string $nextOrderDate = null): Subscription
{
    $product = Product::factory()->create(['is_active' => true]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true, 'stock' => 10]);
    $address = Address::factory()->create(['user_id' => $user->id]);

    return Subscription::create([
        'user_id'         => $user->id,
        'product_id'      => $product->id,
        'variant_id'      => $variant->id,
        'quantity'        => 2,
        'address_id'      => $address->id,
        'plan'            => 'weekly',
        'discount_rate'   => 10.0,
        'status'          => 'active',
        'next_order_date' => $nextOrderDate ?? Carbon::today()->toDateString(),
        'start_date'      => Carbon::today()->subDays(7)->toDateString(),
    ]);
}

test('cron creates order for due subscription', function () {
    $user = cronUser();
    $sub  = dueSubscription($user);

    $this->artisan('subscriptions:process-orders')->assertSuccessful();

    $this->assertDatabaseCount('orders', 1);
    $sub->refresh();
    expect($sub->last_order_id)->not->toBeNull();
    expect($sub->next_order_date->toDateString())
        ->toBe(Carbon::today()->addDays(7)->toDateString());
});

test('cron does not create order for paused subscription', function () {
    $user = cronUser();
    $sub  = dueSubscription($user);
    $sub->update(['status' => 'paused', 'pause_until' => Carbon::today()->addDays(3)->toDateString()]);

    $this->artisan('subscriptions:process-orders')->assertSuccessful();

    $this->assertDatabaseCount('orders', 0);
});

test('cron does not create order for future next_order_date', function () {
    $user = cronUser();
    dueSubscription($user, Carbon::today()->addDays(5)->toDateString());

    $this->artisan('subscriptions:process-orders')->assertSuccessful();

    $this->assertDatabaseCount('orders', 0);
});

test('cron skips subscription with insufficient stock', function () {
    $user    = cronUser();
    $product = Product::factory()->create(['is_active' => true]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true, 'stock' => 0]);
    $address = Address::factory()->create(['user_id' => $user->id]);

    Subscription::create([
        'user_id'         => $user->id,
        'product_id'      => $product->id,
        'variant_id'      => $variant->id,
        'quantity'        => 2,
        'address_id'      => $address->id,
        'plan'            => 'weekly',
        'discount_rate'   => 10.0,
        'status'          => 'active',
        'next_order_date' => Carbon::today()->toDateString(),
        'start_date'      => Carbon::today()->subDays(7)->toDateString(),
    ]);

    $this->artisan('subscriptions:process-orders')->assertSuccessful();

    $this->assertDatabaseCount('orders', 0);
});
