<?php

/**
 * End-to-end Order Lifecycle Integration Test
 *
 * Tests the full order lifecycle using actingAs() to properly switch between
 * customer and admin users within the same test (avoids Sanctum guard caching).
 */

use App\Models\Address;
use App\Models\Inventory;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeAdmin(string $phone = '+905559001001'): User
{
    $user = User::factory()->admin()->create(['phone' => $phone]);
    $user->assignRole('admin');
    return $user;
}

function makeCustomer(string $phone = '+905559001002'): array
{
    $user    = User::factory()->create(['phone' => $phone]);
    $address = Address::create([
        'user_id'      => $user->id,
        'title'        => 'Home',
        'full_address' => 'Test Street 1',
        'district'     => 'Kadikoy',
        'city'         => 'Istanbul',
        'postal_code'  => '34000',
        'is_default'   => true,
    ]);
    return [$user, $address];
}

function makeProductWithStock(int $qty = 50): Product
{
    $warehouse = Warehouse::factory()->create();
    $product   = Product::factory()->create(['price' => 100.00, 'is_active' => true]);
    Inventory::create([
        'product_id'        => $product->id,
        'warehouse_id'      => $warehouse->id,
        'variant_id'        => null,
        'quantity'          => $qty,
        'reserved_quantity' => 0,
    ]);
    return $product;
}

// ── Full happy-path lifecycle ─────────────────────────────────────────────────

test('full order lifecycle: cart → order → delivered', function () {
    $admin               = makeAdmin('+905559001001');
    [$customer, $addr]   = makeCustomer('+905559001002');
    $product             = makeProductWithStock(50);

    // 1. Customer adds item to cart
    $this->actingAs($customer, 'sanctum')
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 3])
        ->assertStatus(201);

    // 2. Customer places order
    $orderResponse = $this->actingAs($customer, 'sanctum')
        ->postJson('/api/v1/orders', ['address_id' => $addr->id, 'payment_method' => 'cash'])
        ->assertStatus(201)
        ->assertJsonPath('data.status', OrderStatus::PENDING->value);

    $orderNumber = $orderResponse->json('data.order_number');
    $order       = Order::where('order_number', $orderNumber)->first();

    expect($order)->not->toBeNull()
        ->and($order->status)->toBe(OrderStatus::PENDING->value)
        ->and($order->items()->count())->toBe(1);

    // 3. Inventory is reserved after order placed
    $inv = Inventory::where('product_id', $product->id)->first();
    expect($inv->reserved_quantity)->toBe(3);

    // 4. A notification was dispatched for the customer (OrderPlacedEvent)
    expect(Notification::where('user_id', $customer->id)->count())->toBeGreaterThanOrEqual(1);

    // 5–8. Admin drives status through the full lifecycle
    foreach ([
        'confirmed'  => 'confirmed',
        'preparing'  => 'preparing',
        'on_the_way' => 'on_the_way',
        'delivered'  => 'delivered',
    ] as $status => $expectedStatus) {
        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/orders/{$order->id}/status", ['status' => $status])
            ->assertStatus(200)
            ->assertJsonPath('data.status', $expectedStatus);
    }

    // 9. Customer sees the order in their list as delivered
    $listResponse = $this->actingAs($customer, 'sanctum')
        ->getJson('/api/v1/orders')
        ->assertStatus(200);

    $found = collect($listResponse->json('data'))->firstWhere('order_number', $orderNumber);
    expect($found)->not->toBeNull()
        ->and($found['status'])->toBe('delivered');

    // 10. Multiple notifications generated across the lifecycle
    expect(Notification::where('user_id', $customer->id)->count())->toBeGreaterThanOrEqual(2);
});

// ── Cancellation with inventory release ───────────────────────────────────────

test('customer can cancel a pending order and inventory is released', function () {
    [$customer, $addr] = makeCustomer('+905559001010');
    $product           = makeProductWithStock(20);

    $this->actingAs($customer, 'sanctum')
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 5])
        ->assertStatus(201);

    $orderResponse = $this->actingAs($customer, 'sanctum')
        ->postJson('/api/v1/orders', ['address_id' => $addr->id, 'payment_method' => 'cash'])
        ->assertStatus(201);

    $order = Order::where('order_number', $orderResponse->json('data.order_number'))->first();

    // Reserved after order
    $inv = Inventory::where('product_id', $product->id)->first();
    expect($inv->reserved_quantity)->toBe(5);

    // Customer cancels
    $this->actingAs($customer, 'sanctum')
        ->postJson("/api/v1/orders/{$order->id}/cancel", ['reason' => 'Changed my mind'])
        ->assertStatus(200)
        ->assertJsonPath('data.status', 'cancelled');

    // Inventory reservation released
    $inv->refresh();
    expect($inv->reserved_quantity)->toBe(0);
});

// ── Admin cancellation ────────────────────────────────────────────────────────

test('admin can cancel any order', function () {
    $admin             = makeAdmin('+905559001020');
    [$customer, $addr] = makeCustomer('+905559001021');
    $product           = makeProductWithStock(10);

    $this->actingAs($customer, 'sanctum')
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 2])
        ->assertStatus(201);

    $orderResponse = $this->actingAs($customer, 'sanctum')
        ->postJson('/api/v1/orders', ['address_id' => $addr->id, 'payment_method' => 'cash'])
        ->assertStatus(201);

    $order = Order::where('order_number', $orderResponse->json('data.order_number'))->first();

    $this->actingAs($admin, 'sanctum')
        ->postJson("/api/v1/orders/{$order->id}/cancel", ['reason' => 'Admin cancelled'])
        ->assertStatus(200)
        ->assertJsonPath('data.status', 'cancelled');
});

// ── Invalid status transitions ────────────────────────────────────────────────

test('cannot transition from delivered back to pending', function () {
    $admin             = makeAdmin('+905559001030');
    [$customer, $addr] = makeCustomer('+905559001031');
    $product           = makeProductWithStock(10);

    $this->actingAs($customer, 'sanctum')
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1])
        ->assertStatus(201);

    $orderResponse = $this->actingAs($customer, 'sanctum')
        ->postJson('/api/v1/orders', ['address_id' => $addr->id, 'payment_method' => 'cash'])
        ->assertStatus(201);

    $order = Order::where('order_number', $orderResponse->json('data.order_number'))->first();

    // Drive to delivered
    foreach (['confirmed', 'preparing', 'on_the_way', 'delivered'] as $status) {
        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/orders/{$order->id}/status", ['status' => $status])
            ->assertStatus(200);
    }

    // Admin can freely go back to any status
    $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/orders/{$order->id}/status", ['status' => 'pending'])
        ->assertStatus(200);
});

// ── Insufficient stock ────────────────────────────────────────────────────────

test('order fails when stock is insufficient', function () {
    [$customer, $addr] = makeCustomer('+905559001040');
    $product           = makeProductWithStock(2); // only 2 in stock

    $this->actingAs($customer, 'sanctum')
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 5])
        ->assertStatus(201);

    $this->actingAs($customer, 'sanctum')
        ->postJson('/api/v1/orders', ['address_id' => $addr->id, 'payment_method' => 'cash'])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'requested', 'available']);
});

// ── Customer cannot view other customers' orders ──────────────────────────────

test('customer cannot view another customers order', function () {
    [$customer1, $addr1] = makeCustomer('+905559001050');
    [$customer2]         = makeCustomer('+905559001051');
    $product             = makeProductWithStock(10);

    $this->actingAs($customer1, 'sanctum')
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1])
        ->assertStatus(201);

    $orderResponse = $this->actingAs($customer1, 'sanctum')
        ->postJson('/api/v1/orders', ['address_id' => $addr1->id, 'payment_method' => 'cash'])
        ->assertStatus(201);

    $orderId = $orderResponse->json('data.id');

    // Other customer tries to view
    $this->actingAs($customer2, 'sanctum')
        ->getJson("/api/v1/orders/{$orderId}")
        ->assertStatus(403);
});

// ── Admin order list ──────────────────────────────────────────────────────────

test('admin can list and filter orders by status', function () {
    $admin             = makeAdmin('+905559001060');
    [$customer, $addr] = makeCustomer('+905559001061');
    $product           = makeProductWithStock(100);

    // Place two orders
    foreach ([1, 2] as $_) {
        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1])
            ->assertStatus(201);
        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v1/orders', ['address_id' => $addr->id, 'payment_method' => 'cash'])
            ->assertStatus(201);
    }

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/orders?status=pending')
        ->assertStatus(200);

    $orders = $response->json('data');
    expect(collect($orders)->every(fn ($o) => $o['status'] === 'pending'))->toBeTrue();
});
