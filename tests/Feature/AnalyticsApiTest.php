<?php

use App\Models\AppConfig;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// ── Helpers ───────────────────────────────────────────────────────────────────

function analyticsAdmin(string $phone = '+905550008001'): array
{
    $user = User::factory()->admin()->create(['phone' => $phone]);
    $user->assignRole('admin');
    $token = $user->createToken('test')->plainTextToken;
    return [$user, $token];
}

function analyticsCustomer(string $phone = '+905550008002'): array
{
    $user  = User::factory()->create(['phone' => $phone]);
    $token = $user->createToken('test')->plainTextToken;
    return [$user, $token];
}

// ── GET /api/v1/config ────────────────────────────────────────────────────────

test('anyone can fetch public app config', function () {
    // Migration already seeds default rows; update existing ones to known values
    AppConfig::where('key', 'app_version_ios')->update(['value' => '2.0.0']);
    AppConfig::where('key', 'force_update')->update(['value' => 'false']);
    AppConfig::where('key', 'min_order_amount')->update(['value' => '50']);

    $response = $this->getJson('/api/v1/config')->assertStatus(200);

    expect($response->json('data.app_version_ios'))->toBe('2.0.0')
        ->and($response->json('data.force_update'))->toBeFalse()
        ->and((float) $response->json('data.min_order_amount'))->toBe(50.0);
});

test('config endpoint returns 200 even with no rows', function () {
    $this->getJson('/api/v1/config')->assertStatus(200)
        ->assertJsonStructure(['data']);
});

// ── GET /api/v1/admin/analytics/dashboard ─────────────────────────────────────

test('admin can fetch dashboard stats', function () {
    [, $token] = analyticsAdmin('+905550008003');

    $response = $this->withToken($token)
        ->getJson('/api/v1/admin/analytics/dashboard')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'today'    => ['orders', 'revenue', 'new_customers'],
                'month'    => ['orders', 'revenue'],
                'all_time' => ['customers', 'orders', 'revenue'],
                'active'   => ['pending_orders', 'low_stock_products'],
            ],
        ]);

    expect($response->json('data.today.orders'))->toBeInt();
});

test('customer cannot access analytics dashboard', function () {
    [, $token] = analyticsCustomer('+905550008004');

    $this->withToken($token)
        ->getJson('/api/v1/admin/analytics/dashboard')
        ->assertStatus(403);
});

test('unauthenticated request to analytics is rejected', function () {
    $this->getJson('/api/v1/admin/analytics/dashboard')->assertStatus(401);
});

// ── GET /api/v1/admin/analytics/revenue ──────────────────────────────────────

test('admin can fetch revenue report', function () {
    [, $token] = analyticsAdmin('+905550008005');
    $user      = User::factory()->create(['phone' => '+905550008099']);
    Order::factory()->create([
        'customer_id' => $user->id,
        'status'      => 'delivered',
        'total'       => 200.00,
        'subtotal'    => 200.00,
        'tax_amount'  => 0,
        'created_at'  => today(),
    ]);

    $response = $this->withToken($token)
        ->getJson('/api/v1/admin/analytics/revenue?from=' . today()->toDateString() . '&to=' . today()->toDateString())
        ->assertStatus(200)
        ->assertJsonStructure(['data', 'range' => ['from', 'to']]);

    $rows = $response->json('data');
    expect($rows)->not->toBeEmpty()
        ->and((float) $rows[0]['revenue'])->toBe(200.0);
});

test('revenue report defaults to 30-day range', function () {
    [, $token] = analyticsAdmin('+905550008006');

    $response = $this->withToken($token)
        ->getJson('/api/v1/admin/analytics/revenue')
        ->assertStatus(200);

    expect($response->json('range'))->toHaveKeys(['from', 'to']);
});

// ── GET /api/v1/admin/analytics/top-products ──────────────────────────────────

test('admin can fetch top products', function () {
    [, $token] = analyticsAdmin('+905550008007');

    $this->withToken($token)
        ->getJson('/api/v1/admin/analytics/top-products')
        ->assertStatus(200)
        ->assertJsonStructure(['data']);
});

test('top-products limit is capped at 50', function () {
    [, $token] = analyticsAdmin('+905550008008');

    // Should not crash with limit=999
    $this->withToken($token)
        ->getJson('/api/v1/admin/analytics/top-products?limit=999')
        ->assertStatus(200);
});

// ── GET /api/v1/admin/analytics/top-customers ─────────────────────────────────

test('admin can fetch top customers', function () {
    [, $token] = analyticsAdmin('+905550008009');

    $this->withToken($token)
        ->getJson('/api/v1/admin/analytics/top-customers')
        ->assertStatus(200)
        ->assertJsonStructure(['data']);
});

// ── GET /api/v1/admin/analytics/order-statuses ───────────────────────────────

test('admin can fetch order status breakdown', function () {
    [, $token] = analyticsAdmin('+905550008010');
    Order::factory()->count(3)->create(['status' => 'pending']);

    $response = $this->withToken($token)
        ->getJson('/api/v1/admin/analytics/order-statuses')
        ->assertStatus(200)
        ->assertJsonStructure(['data']);

    $data = $response->json('data');
    expect($data)->not->toBeEmpty();
    $pending = collect($data)->firstWhere('status', 'pending');
    expect($pending)->not->toBeNull()
        ->and($pending['count'])->toBe(3)
        ->and($pending['label'])->toBe('Pending');
});

test('order-statuses accepts optional date range', function () {
    [, $token] = analyticsAdmin('+905550008011');

    $this->withToken($token)
        ->getJson('/api/v1/admin/analytics/order-statuses?from=2025-01-01&to=2025-12-31')
        ->assertStatus(200);
});

// ── Artisan command ───────────────────────────────────────────────────────────

test('analytics:aggregate-daily command runs without error', function () {
    $this->artisan('analytics:aggregate-daily ' . now()->subDay()->toDateString())
        ->assertExitCode(0);
});

test('analytics:aggregate-daily is idempotent', function () {
    $date = now()->subDay()->toDateString();
    $this->artisan("analytics:aggregate-daily {$date}")->assertExitCode(0);
    $this->artisan("analytics:aggregate-daily {$date}")->assertExitCode(0);

    $this->assertDatabaseCount('daily_stats', 1);
});
