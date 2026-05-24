<?php

use App\Models\Inventory;
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

function invAdminUser(): array
{
    $user = User::factory()->admin()->create(['phone' => '+905550002001']);
    $user->assignRole('admin');
    $token = $user->createToken('test')->plainTextToken;
    return [$user, $token];
}

function invCustomerUser(): array
{
    $user  = User::factory()->create(['phone' => '+905550002002']);
    $token = $user->createToken('test')->plainTextToken;
    return [$user, $token];
}

// ── GET /api/v1/inventory/warehouses ─────────────────────────────────────────

test('authenticated user can list active warehouses', function () {
    Warehouse::factory()->count(3)->create(['is_active' => true]);
    Warehouse::factory()->inactive()->create();

    [, $token] = invCustomerUser();

    $this->withToken($token)
        ->getJson('/api/v1/inventory/warehouses')
        ->assertStatus(200)
        ->assertJsonPath('data.0.id', fn ($id) => is_int($id))
        ->assertJsonCount(3, 'data');
});

test('unauthenticated request to warehouses is rejected', function () {
    $this->getJson('/api/v1/inventory/warehouses')
        ->assertStatus(401);
});

// ── GET /api/v1/inventory/stock/{product} ─────────────────────────────────────

test('authenticated user can view stock for a product', function () {
    $product = Product::factory()->create();
    $wh1     = Warehouse::factory()->create();
    $wh2     = Warehouse::factory()->create();

    Inventory::create(['product_id' => $product->id, 'warehouse_id' => $wh1->id, 'quantity' => 10, 'reserved_quantity' => 2]);
    Inventory::create(['product_id' => $product->id, 'warehouse_id' => $wh2->id, 'quantity' => 20, 'reserved_quantity' => 0]);

    [, $token] = invCustomerUser();

    $this->withToken($token)
        ->getJson("/api/v1/inventory/stock/{$product->id}")
        ->assertStatus(200)
        ->assertJsonPath('product_id', $product->id)
        ->assertJsonCount(2, 'stock');
});

test('stock response includes available_quantity and is_low_stock', function () {
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    Inventory::create(['product_id' => $product->id, 'warehouse_id' => $wh->id, 'quantity' => 3, 'reserved_quantity' => 0]);

    [, $token] = invCustomerUser();

    $response = $this->withToken($token)
        ->getJson("/api/v1/inventory/stock/{$product->id}")
        ->assertStatus(200);

    $stock = $response->json('stock.0');
    expect($stock['available_quantity'])->toBe(3)
        ->and($stock['is_low_stock'])->toBeTrue();
});

// ── POST /api/v1/inventory/receive ────────────────────────────────────────────

test('admin can receive stock', function () {
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    [, $token] = invAdminUser();

    $this->withToken($token)
        ->postJson('/api/v1/inventory/receive', [
            'product_id'   => $product->id,
            'warehouse_id' => $wh->id,
            'quantity'     => 50,
            'reason'       => 'New shipment',
        ])
        ->assertStatus(200)
        ->assertJsonPath('inventory.quantity', 50);

    $this->assertDatabaseHas('inventory', [
        'product_id'   => $product->id,
        'warehouse_id' => $wh->id,
        'quantity'     => 50,
    ]);
    $this->assertDatabaseHas('stock_movements', [
        'product_id'   => $product->id,
        'warehouse_id' => $wh->id,
        'type'         => 'in',
        'quantity'     => 50,
    ]);
});

test('customer cannot receive stock', function () {
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    [, $token] = invCustomerUser();

    $this->withToken($token)
        ->postJson('/api/v1/inventory/receive', [
            'product_id'   => $product->id,
            'warehouse_id' => $wh->id,
            'quantity'     => 10,
        ])
        ->assertStatus(403);
});

test('receive validates quantity minimum of 1', function () {
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    [, $token] = invAdminUser();

    $this->withToken($token)
        ->postJson('/api/v1/inventory/receive', [
            'product_id'   => $product->id,
            'warehouse_id' => $wh->id,
            'quantity'     => 0,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['quantity']);
});

// ── POST /api/v1/inventory/dispatch ───────────────────────────────────────────

test('admin can dispatch stock', function () {
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    [, $token] = invAdminUser();

    // Seed stock first
    $this->withToken($token)
        ->postJson('/api/v1/inventory/receive', [
            'product_id'   => $product->id,
            'warehouse_id' => $wh->id,
            'quantity'     => 30,
        ]);

    $this->withToken($token)
        ->postJson('/api/v1/inventory/dispatch', [
            'product_id'   => $product->id,
            'warehouse_id' => $wh->id,
            'quantity'     => 10,
        ])
        ->assertStatus(200)
        ->assertJsonPath('inventory.quantity', 20);
});

test('dispatch returns 422 with insufficient stock details', function () {
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    [, $token] = invAdminUser();

    $this->withToken($token)
        ->postJson('/api/v1/inventory/dispatch', [
            'product_id'   => $product->id,
            'warehouse_id' => $wh->id,
            'quantity'     => 999,
        ])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'requested', 'available']);
});

// ── POST /api/v1/inventory/adjust ────────────────────────────────────────────

test('admin can adjust stock to absolute quantity', function () {
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    [, $token] = invAdminUser();

    // Seed stock first
    $this->withToken($token)
        ->postJson('/api/v1/inventory/receive', [
            'product_id'   => $product->id,
            'warehouse_id' => $wh->id,
            'quantity'     => 50,
        ]);

    $this->withToken($token)
        ->postJson('/api/v1/inventory/adjust', [
            'product_id'   => $product->id,
            'warehouse_id' => $wh->id,
            'quantity'     => 35,
            'reason'       => 'Physical inventory count',
        ])
        ->assertStatus(200)
        ->assertJsonPath('inventory.quantity', 35);

    $this->assertDatabaseHas('stock_movements', [
        'type'     => 'adjustment',
        'quantity' => -15,  // delta
    ]);
});

test('adjust requires reason field', function () {
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    [, $token] = invAdminUser();

    $this->withToken($token)
        ->postJson('/api/v1/inventory/adjust', [
            'product_id'   => $product->id,
            'warehouse_id' => $wh->id,
            'quantity'     => 10,
            // missing reason
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['reason']);
});

// ── POST /api/v1/inventory/transfer ───────────────────────────────────────────

test('admin can transfer stock between warehouses', function () {
    $product = Product::factory()->create();
    $src     = Warehouse::factory()->create();
    $dst     = Warehouse::factory()->create();

    [, $token] = invAdminUser();

    // Seed source
    $this->withToken($token)
        ->postJson('/api/v1/inventory/receive', [
            'product_id'   => $product->id,
            'warehouse_id' => $src->id,
            'quantity'     => 40,
        ]);

    $this->withToken($token)
        ->postJson('/api/v1/inventory/transfer', [
            'product_id'       => $product->id,
            'from_warehouse_id' => $src->id,
            'to_warehouse_id'   => $dst->id,
            'quantity'          => 15,
        ])
        ->assertStatus(200)
        ->assertJsonPath('message', fn ($m) => str_contains($m, '15'));

    $this->assertDatabaseHas('inventory', ['warehouse_id' => $src->id, 'quantity' => 25]);
    $this->assertDatabaseHas('inventory', ['warehouse_id' => $dst->id, 'quantity' => 15]);
});

test('transfer returns 422 when source has insufficient stock', function () {
    $product = Product::factory()->create();
    $src     = Warehouse::factory()->create();
    $dst     = Warehouse::factory()->create();

    [, $token] = invAdminUser();

    $this->withToken($token)
        ->postJson('/api/v1/inventory/transfer', [
            'product_id'        => $product->id,
            'from_warehouse_id' => $src->id,
            'to_warehouse_id'   => $dst->id,
            'quantity'          => 999,
        ])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'requested', 'available']);
});

test('transfer rejects same source and destination warehouse', function () {
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    [, $token] = invAdminUser();

    $this->withToken($token)
        ->postJson('/api/v1/inventory/transfer', [
            'product_id'        => $product->id,
            'from_warehouse_id' => $wh->id,
            'to_warehouse_id'   => $wh->id,
            'quantity'          => 5,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['to_warehouse_id']);
});

// ── GET /api/v1/inventory/low-stock ───────────────────────────────────────────

test('admin can view low stock report', function () {
    $product = Product::factory()->create();
    Inventory::create(['product_id' => $product->id, 'warehouse_id' => Warehouse::factory()->create()->id, 'quantity' => 2, 'reserved_quantity' => 0]);
    Inventory::create(['product_id' => $product->id, 'warehouse_id' => Warehouse::factory()->create()->id, 'quantity' => 50, 'reserved_quantity' => 0]);

    [, $token] = invAdminUser();

    $response = $this->withToken($token)
        ->getJson('/api/v1/inventory/low-stock')
        ->assertStatus(200);

    expect($response->json('summary.low_stock_count'))->toBe(1)
        ->and($response->json('threshold'))->toBe(5);
});

test('customer cannot access low-stock endpoint', function () {
    [, $token] = invCustomerUser();

    $this->withToken($token)
        ->getJson('/api/v1/inventory/low-stock')
        ->assertStatus(403);
});

// ── GET /api/v1/inventory/movements ───────────────────────────────────────────

test('admin can list stock movements', function () {
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    [, $token] = invAdminUser();

    // Create movements via receive
    $this->withToken($token)->postJson('/api/v1/inventory/receive', [
        'product_id' => $product->id, 'warehouse_id' => $wh->id, 'quantity' => 10,
    ]);

    $response = $this->withToken($token)
        ->getJson('/api/v1/inventory/movements')
        ->assertStatus(200);

    expect($response->json('data'))->not->toBeEmpty();
    expect($response->json('data.0.type_label'))->toBe('Stock In');
});
