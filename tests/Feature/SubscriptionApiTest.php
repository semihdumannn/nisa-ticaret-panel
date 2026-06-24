<?php

use App\Models\Address;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function subUser(): User
{
    return User::factory()->create(['role' => 'customer', 'is_active' => true]);
}

function subFixtures(User $user): array
{
    $product = Product::factory()->create(['is_active' => true]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true]);
    $address = Address::factory()->create(['user_id' => $user->id]);

    return compact('product', 'variant', 'address');
}

test('user can create a subscription', function () {
    $user = subUser();
    ['product' => $product, 'variant' => $variant, 'address' => $address] = subFixtures($user);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/subscriptions', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity'   => 2,
            'address_id' => $address->id,
            'plan'       => 'weekly',
            'start_date' => now()->addDay()->toDateString(),
        ])
        ->assertCreated()
        ->assertJsonPath('plan', 'weekly')
        ->assertJsonPath('discount_rate', 10.0);
});

test('user can list their subscriptions', function () {
    $user = subUser();
    ['product' => $product, 'variant' => $variant, 'address' => $address] = subFixtures($user);
    Subscription::create([
        'user_id'        => $user->id,
        'product_id'     => $product->id,
        'variant_id'     => $variant->id,
        'quantity'       => 1,
        'address_id'     => $address->id,
        'plan'           => 'monthly',
        'discount_rate'  => 5.0,
        'status'         => 'active',
        'next_order_date' => now()->addDays(30)->toDateString(),
        'start_date'     => now()->toDateString(),
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/subscriptions')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

test('user can cancel a subscription', function () {
    $user = subUser();
    ['product' => $product, 'variant' => $variant, 'address' => $address] = subFixtures($user);
    $sub = Subscription::create([
        'user_id'        => $user->id,
        'product_id'     => $product->id,
        'variant_id'     => $variant->id,
        'quantity'       => 1,
        'address_id'     => $address->id,
        'plan'           => 'weekly',
        'discount_rate'  => 10.0,
        'status'         => 'active',
        'next_order_date' => now()->addDays(7)->toDateString(),
        'start_date'     => now()->toDateString(),
    ]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/subscriptions/{$sub->id}")
        ->assertNoContent();

    $this->assertDatabaseHas('subscriptions', ['id' => $sub->id, 'status' => 'cancelled']);
});

test('user cannot access another users subscription', function () {
    $user1 = subUser();
    $user2 = subUser();
    ['product' => $product, 'variant' => $variant, 'address' => $address] = subFixtures($user1);
    $sub = Subscription::create([
        'user_id'        => $user1->id,
        'product_id'     => $product->id,
        'variant_id'     => $variant->id,
        'quantity'       => 1,
        'address_id'     => $address->id,
        'plan'           => 'monthly',
        'discount_rate'  => 5.0,
        'status'         => 'active',
        'next_order_date' => now()->addDays(30)->toDateString(),
        'start_date'     => now()->toDateString(),
    ]);

    $this->actingAs($user2, 'sanctum')
        ->deleteJson("/api/v1/subscriptions/{$sub->id}")
        ->assertNotFound();
});
