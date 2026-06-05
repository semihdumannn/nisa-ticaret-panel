<?php

use App\Models\Address;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// ── Helpers ───────────────────────────────────────────────────────────────────

function orderAdmin(): array
{
    $user = User::factory()->admin()->create(['phone' => '+905550005001']);
    $user->assignRole('admin');
    $token = $user->createToken('test')->plainTextToken;
    return [$user, $token];
}

function orderCustomer(string $phone = '+905550005002'): array
{
    $user  = User::factory()->create(['phone' => $phone]);
    $token = $user->createToken('test')->plainTextToken;
    return [$user, $token];
}

/**
 * Seed a product with stock, create user+address, fill cart, return all context.
 */
function scaffoldOrderContext(string $phone = '+905550005003'): array
{
    [$user, $token] = orderCustomer($phone);
    $product        = Product::factory()->create(['price' => 40, 'tax_rate' => 18]);
    $wh             = Warehouse::factory()->create();
    $address        = Address::create(['user_id' => $user->id, 'full_address' => '123 Main St', 'city' => 'Istanbul']);

    Inventory::create(['product_id' => $product->id, 'warehouse_id' => $wh->id, 'quantity' => 50, 'reserved_quantity' => 0]);

    // Fill cart
    \Illuminate\Support\Facades\Http::fake(); // no HTTP calls needed
    app(\App\Modules\Order\Application\UseCases\GetOrCreateCartUseCase::class)->execute($user->id);
    app(\App\Modules\Order\Application\UseCases\AddToCartUseCase::class)->execute(
        app(\App\Modules\Order\Domain\Contracts\CartRepositoryInterface::class)->getOrCreate($user->id),
        new \App\Modules\Order\Application\DTOs\AddCartItemDTO($product->id, 3),
    );

    return [$user, $token, $product, $wh, $address];
}

// ── POST /api/v1/orders ───────────────────────────────────────────────────────

test('customer can create order from cart', function () {
    [$user, $token, $product, $wh, $address] = scaffoldOrderContext('+905550005010');

    $response = $this->withToken($token)
        ->postJson('/api/v1/orders', [
            'address_id'     => $address->id,
            'payment_method' => 'cash',
        ])
        ->assertStatus(201);

    expect($response->json('data.status'))->toBe('pending')
        ->and($response->json('data.order_number'))->toMatch('/^ORD-/')
        ->and($response->json('data.items'))->toHaveCount(1);

    // Cart cleared
    $this->assertDatabaseCount('cart_items', 0);
    // Stock reserved
    $this->assertDatabaseHas('inventory', ['product_id' => $product->id, 'reserved_quantity' => 3]);
});

test('order creation fails with empty cart', function () {
    [$user, $token] = orderCustomer('+905550005011');
    $address        = Address::create(['user_id' => $user->id, 'full_address' => 'x', 'city' => 'Y']);

    $this->withToken($token)
        ->postJson('/api/v1/orders', ['address_id' => $address->id])
        ->assertStatus(422)
        ->assertJsonPath('message', fn ($m) => str_contains(strtolower($m), 'empty') || str_contains(strtolower($m), 'cart'));
});

test('order creation fails with insufficient stock', function () {
    [$user, $token] = orderCustomer('+905550005012');
    $product        = Product::factory()->create(['price' => 20, 'tax_rate' => 0]);
    $address        = Address::create(['user_id' => $user->id, 'full_address' => 'x', 'city' => 'Y']);

    // No inventory — add to cart anyway
    app(\App\Modules\Order\Domain\Contracts\CartRepositoryInterface::class)
        ->addItem(
            app(\App\Modules\Order\Domain\Contracts\CartRepositoryInterface::class)->getOrCreate($user->id),
            $product->id, null, 99,
        );

    $this->withToken($token)
        ->postJson('/api/v1/orders', ['address_id' => $address->id])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'requested', 'available']);
});

test('order creation validates address_id', function () {
    [$user, $token] = orderCustomer('+905550005013');

    $this->withToken($token)
        ->postJson('/api/v1/orders', ['address_id' => 99999])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['address_id']);
});

// ── GET /api/v1/orders ────────────────────────────────────────────────────────

test('customer can list their own orders', function () {
    [$user, $token] = orderCustomer('+905550005014');
    Order::factory()->count(2)->create(['customer_id' => $user->id]);
    Order::factory()->count(3)->create(); // other customers

    $response = $this->withToken($token)
        ->getJson('/api/v1/orders')
        ->assertStatus(200);

    expect($response->json('data'))->toHaveCount(2);
});

// ── GET /api/v1/orders/{order} ────────────────────────────────────────────────

test('customer can view their own order', function () {
    [$user, $token] = orderCustomer('+905550005015');
    $order          = Order::factory()->create(['customer_id' => $user->id]);

    $this->withToken($token)
        ->getJson("/api/v1/orders/{$order->id}")
        ->assertStatus(200)
        ->assertJsonPath('data.id', $order->id);
});

test('customer cannot view another customers order', function () {
    $user1 = User::factory()->create(['phone' => '+905550005016']);
    $user2 = User::factory()->create(['phone' => '+905550005017']);
    $order = Order::factory()->create(['customer_id' => $user2->id]);

    // actingAs resets the guard cache; user1 cannot see user2's order
    $this->actingAs($user1, 'sanctum')
        ->getJson("/api/v1/orders/{$order->id}")
        ->assertStatus(403);
});

// ── POST /api/v1/orders/{order}/cancel ───────────────────────────────────────

test('customer can cancel a pending order', function () {
    [$user, $token] = orderCustomer('+905550005018');
    $order          = Order::factory()->create(['customer_id' => $user->id, 'status' => 'pending']);

    $this->withToken($token)
        ->postJson("/api/v1/orders/{$order->id}/cancel", ['reason' => 'Changed mind'])
        ->assertStatus(200)
        ->assertJsonPath('data.status', 'cancelled');
});

test('customer can cancel a delivered order', function () {
    [$user, $token] = orderCustomer('+905550005019');
    $order          = Order::factory()->delivered()->create(['customer_id' => $user->id]);

    $this->withToken($token)
        ->postJson("/api/v1/orders/{$order->id}/cancel")
        ->assertStatus(200);
});

test('customer cannot cancel another users order', function () {
    $user1 = User::factory()->create(['phone' => '+905550005020']);
    $user2 = User::factory()->create(['phone' => '+905550005021']);
    $order = Order::factory()->create(['customer_id' => $user2->id]);

    $this->actingAs($user1, 'sanctum')
        ->postJson("/api/v1/orders/{$order->id}/cancel")
        ->assertStatus(403);
});

// ── GET /api/v1/admin/orders ──────────────────────────────────────────────────

test('admin can list all orders', function () {
    [, $adminToken] = orderAdmin();
    Order::factory()->count(5)->create();

    $this->withToken($adminToken)
        ->getJson('/api/v1/admin/orders')
        ->assertStatus(200);
});

test('customer cannot access admin orders endpoint', function () {
    [$user, $token] = orderCustomer('+905550005022');

    $this->withToken($token)
        ->getJson('/api/v1/admin/orders')
        ->assertStatus(403);
});

// ── PUT /api/v1/admin/orders/{order}/status ───────────────────────────────────

test('admin can advance order status', function () {
    [, $adminToken] = orderAdmin();
    $order          = Order::factory()->create(['status' => 'pending']);

    $this->withToken($adminToken)
        ->putJson("/api/v1/admin/orders/{$order->id}/status", [
            'status' => 'confirmed',
            'note'   => 'Verified payment',
        ])
        ->assertStatus(200)
        ->assertJsonPath('data.status', 'confirmed');

    $this->assertDatabaseHas('order_status_history', [
        'order_id' => $order->id,
        'status'   => 'confirmed',
    ]);
});

test('admin can make any status transition', function () {
    [, $adminToken] = orderAdmin();
    $order          = Order::factory()->create(['status' => 'pending']);

    $this->withToken($adminToken)
        ->putJson("/api/v1/admin/orders/{$order->id}/status", ['status' => 'delivered'])
        ->assertStatus(200);
});

test('status update validates status enum', function () {
    [, $adminToken] = orderAdmin();
    $order          = Order::factory()->create();

    $this->withToken($adminToken)
        ->putJson("/api/v1/admin/orders/{$order->id}/status", ['status' => 'flying'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('order response includes status label and can_cancel flag', function () {
    [$user, $token] = orderCustomer('+905550005023');
    $order          = Order::factory()->create(['customer_id' => $user->id, 'status' => 'pending']);

    $response = $this->withToken($token)
        ->getJson("/api/v1/orders/{$order->id}")
        ->assertStatus(200);

    expect($response->json('data.status_label'))->toBe('Pending')
        ->and($response->json('data.can_cancel'))->toBeTrue();
});
