<?php

use App\Models\Address;
use App\Models\Campaign;
use App\Models\Coupon;
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

function campUser(string $phone = '+905550007001'): array
{
    $user  = User::factory()->create(['phone' => $phone]);
    $token = $user->createToken('test')->plainTextToken;
    return [$user, $token];
}

function scaffoldCouponOrderContext(string $phone = '+905550007050'): array
{
    [$user, $token] = campUser($phone);
    $product        = Product::factory()->create(['price' => 100, 'tax_rate' => 0]);
    $wh             = Warehouse::factory()->create();
    $address        = Address::create(['user_id' => $user->id, 'full_address' => '123 Main St', 'city' => 'Istanbul']);

    Inventory::create(['product_id' => $product->id, 'warehouse_id' => $wh->id, 'quantity' => 50, 'reserved_quantity' => 0]);

    // Fill cart (2 × ₺100 = ₺200 subtotal)
    app(\App\Modules\Order\Domain\Contracts\CartRepositoryInterface::class)
        ->addItem(
            app(\App\Modules\Order\Domain\Contracts\CartRepositoryInterface::class)->getOrCreate($user->id),
            $product->id, null, 2,
        );

    return [$user, $token, $product, $wh, $address];
}

// ── GET /api/v1/campaigns ─────────────────────────────────────────────────────

test('anyone can list active campaigns', function () {
    Campaign::factory()->count(3)->create();
    Campaign::factory()->inactive()->create();
    Campaign::factory()->expired()->create();

    $response = $this->getJson('/api/v1/campaigns')
        ->assertStatus(200);

    // Only the 3 active ones
    expect($response->json('data'))->toHaveCount(3);
});

test('campaigns list is filtered by product_id', function () {
    $product = Product::factory()->create();

    $matched = Campaign::factory()->create();
    $matched->products()->attach($product->id);

    Campaign::factory()->create(); // unrelated campaign

    $response = $this->getJson("/api/v1/campaigns?product_id={$product->id}")
        ->assertStatus(200);

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.id'))->toBe($matched->id);
});

test('campaign resource includes type_label', function () {
    Campaign::factory()->percentage(15)->create();

    $response = $this->getJson('/api/v1/campaigns')->assertStatus(200);

    expect($response->json('data.0.type_label'))->toBe('Percentage');
});

// ── POST /api/v1/coupons/validate ────────────────────────────────────────────

test('authenticated user can validate a valid coupon', function () {
    [$user, $token] = campUser('+905550007002');
    Coupon::factory()->percentage(10, 'VALID10')->create();

    $response = $this->withToken($token)
        ->postJson('/api/v1/coupons/validate', [
            'code'     => 'VALID10',
            'subtotal' => 200.0,
        ])
        ->assertStatus(200);

    expect($response->json('data.code'))->toBe('VALID10')
        ->and((float) $response->json('discount'))->toBe(20.0);
});

test('validating an invalid coupon code returns 422', function () {
    [$user, $token] = campUser('+905550007003');

    $this->withToken($token)
        ->postJson('/api/v1/coupons/validate', [
            'code'     => 'BADCODE',
            'subtotal' => 100.0,
        ])
        ->assertStatus(422)
        ->assertJsonPath('message', fn ($m) => str_contains(strtolower($m), 'invalid') || str_contains(strtolower($m), 'expired'));
});

test('validating an expired coupon returns 422', function () {
    [$user, $token] = campUser('+905550007004');
    Coupon::factory()->expired()->create(['code' => 'OLDCODE']);

    $this->withToken($token)
        ->postJson('/api/v1/coupons/validate', [
            'code'     => 'OLDCODE',
            'subtotal' => 100.0,
        ])
        ->assertStatus(422);
});

test('validating a coupon with usage limit reached returns 422', function () {
    [$user, $token] = campUser('+905550007005');
    Coupon::factory()->usageLimitReached()->create(['code' => 'MAXEDOUT']);

    $this->withToken($token)
        ->postJson('/api/v1/coupons/validate', [
            'code'     => 'MAXEDOUT',
            'subtotal' => 100.0,
        ])
        ->assertStatus(422);
});

test('validating coupon with unmet min purchase returns 422', function () {
    [$user, $token] = campUser('+905550007006');
    Coupon::factory()->withMinPurchase(500)->create(['code' => 'HIGHMIN']);

    $this->withToken($token)
        ->postJson('/api/v1/coupons/validate', [
            'code'     => 'HIGHMIN',
            'subtotal' => 100.0,
        ])
        ->assertStatus(422)
        ->assertJsonPath('min_amount', fn ($v) => (float) $v === 500.0);
});

test('unauthenticated request to validate coupon is rejected', function () {
    $this->postJson('/api/v1/coupons/validate', [
        'code'     => 'X',
        'subtotal' => 100,
    ])->assertStatus(401);
});

// ── Order creation with coupon ────────────────────────────────────────────────

test('order can be created with a valid coupon and discount is applied', function () {
    [$user, $token,, , $address] = scaffoldCouponOrderContext('+905550007010');
    // cart = 2 × ₺100 = ₺200 subtotal
    Coupon::factory()->fixedAmount(30, 'SAVE30')->create();

    $response = $this->withToken($token)
        ->postJson('/api/v1/orders', [
            'address_id'  => $address->id,
            'coupon_code' => 'SAVE30',
        ])
        ->assertStatus(201);

    expect((float) $response->json('data.discount_amount'))->toBe(30.0)
        ->and((float) $response->json('data.total'))->toBe(170.0);
});

test('coupon usage is recorded after successful order', function () {
    [$user, $token,, , $address] = scaffoldCouponOrderContext('+905550007011');
    $coupon = Coupon::factory()->percentage(10, 'TRACK10')->create();

    $this->withToken($token)
        ->postJson('/api/v1/orders', [
            'address_id'  => $address->id,
            'coupon_code' => 'TRACK10',
        ])
        ->assertStatus(201);

    $this->assertDatabaseHas('coupon_usage', [
        'coupon_id' => $coupon->id,
        'user_id'   => $user->id,
    ]);

    // usage_count incremented
    $this->assertDatabaseHas('coupons', [
        'id'          => $coupon->id,
        'usage_count' => 1,
    ]);
});

test('order creation fails with invalid coupon code', function () {
    [$user, $token,, , $address] = scaffoldCouponOrderContext('+905550007012');

    $this->withToken($token)
        ->postJson('/api/v1/orders', [
            'address_id'  => $address->id,
            'coupon_code' => 'NONEXISTENT',
        ])
        ->assertStatus(422)
        ->assertJsonPath('message', fn ($m) => str_contains(strtolower($m), 'invalid') || str_contains(strtolower($m), 'expired'));
});

test('order creation fails when coupon min purchase not met', function () {
    [$user, $token,, , $address] = scaffoldCouponOrderContext('+905550007013');
    // Cart subtotal = ₺200, require ₺500 minimum
    Coupon::factory()->withMinPurchase(500)->create(['code' => 'NEED500']);

    $this->withToken($token)
        ->postJson('/api/v1/orders', [
            'address_id'  => $address->id,
            'coupon_code' => 'NEED500',
        ])
        ->assertStatus(422)
        ->assertJsonPath('min_amount', fn ($v) => (float) $v === 500.0);
});

test('order can be created without coupon (coupon_code is optional)', function () {
    [$user, $token,, , $address] = scaffoldCouponOrderContext('+905550007014');

    $response = $this->withToken($token)
        ->postJson('/api/v1/orders', [
            'address_id' => $address->id,
        ])
        ->assertStatus(201);

    expect((float) $response->json('data.discount_amount'))->toBe(0.0);
});

// ── GET /api/v1/coupons ───────────────────────────────────────────────────────

test('customer can list active coupons', function () {
    $customer = \App\Models\User::factory()->create(['role' => 'customer', 'is_active' => true]);
    \App\Models\Coupon::factory()->create(['is_active' => true, 'end_date' => now()->addDays(30)]);
    \App\Models\Coupon::factory()->create(['is_active' => false]);

    $this->actingAs($customer, 'sanctum')
        ->getJson('/api/v1/coupons')
        ->assertOk()
        ->assertJsonStructure(['coupons' => [['id', 'code', 'type', 'discount_value']]]);
});

// ── Admin coupon CRUD ─────────────────────────────────────────────────────────

test('admin can create a coupon', function () {
    $admin = \App\Models\User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $admin->assignRole('admin');

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/admin/coupons', [
            'code'           => 'TEST50',
            'type'           => 'fixed_amount',
            'discount_value' => 50.0,
            'is_active'      => true,
        ])
        ->assertCreated()
        ->assertJsonPath('data.code', 'TEST50');
});

test('admin can deactivate a coupon', function () {
    $admin  = \App\Models\User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $admin->assignRole('admin');
    $coupon = \App\Models\Coupon::factory()->create(['is_active' => true]);

    $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/v1/admin/coupons/{$coupon->id}")
        ->assertNoContent();

    $this->assertDatabaseHas('coupons', ['id' => $coupon->id, 'is_active' => false]);
});

// ── Percentage coupon cap ─────────────────────────────────────────────────────

test('percentage coupon discount is capped at max_discount_amount', function () {
    [$user, $token,, , $address] = scaffoldCouponOrderContext('+905550007015');
    // Cart = ₺200 subtotal; 50% would be ₺100, capped at ₺40
    Coupon::factory()->create([
        'code'                => 'CAPPED',
        'type'                => 'percentage',
        'value'               => 50,
        'max_discount_amount' => 40,
        'start_date'          => now()->subDay(),
        'end_date'            => now()->addMonth(),
    ]);

    $response = $this->withToken($token)
        ->postJson('/api/v1/orders', [
            'address_id'  => $address->id,
            'coupon_code' => 'CAPPED',
        ])
        ->assertStatus(201);

    expect((float) $response->json('data.discount_amount'))->toBe(40.0)
        ->and((float) $response->json('data.total'))->toBe(160.0);
});
