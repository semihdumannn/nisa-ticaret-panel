<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function cartUser(string $phone = '+905550003001'): array
{
    $user  = User::factory()->create(['phone' => $phone]);
    $token = $user->createToken('test')->plainTextToken;
    return [$user, $token];
}

// ── GET /api/v1/cart ──────────────────────────────────────────────────────────

test('authenticated user gets an empty cart on first request', function () {
    [, $token] = cartUser();

    $this->withToken($token)
        ->getJson('/api/v1/cart')
        ->assertStatus(200)
        ->assertJsonPath('data.item_count', 0)
        ->assertJsonPath('data.items', []);
});

test('unauthenticated request to cart is rejected', function () {
    $this->getJson('/api/v1/cart')->assertStatus(401);
});

// ── POST /api/v1/cart/items ───────────────────────────────────────────────────

test('user can add a product to cart', function () {
    [$user, $token] = cartUser('+905550003002');
    $product        = Product::factory()->create(['price' => 25]);

    $response = $this->withToken($token)
        ->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity'   => 3,
        ])
        ->assertStatus(201);

    expect($response->json('data.item_count'))->toBe(3);
});

test('adding same product twice increments quantity', function () {
    [$user, $token] = cartUser('+905550003003');
    $product        = Product::factory()->create();

    $this->withToken($token)->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 2]);
    $response = $this->withToken($token)->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 3]);

    expect($response->json('data.item_count'))->toBe(5);
});

test('add to cart validates quantity minimum of 1', function () {
    [$user, $token] = cartUser('+905550003004');
    $product        = Product::factory()->create();

    $this->withToken($token)
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 0])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['quantity']);
});

test('add to cart validates product exists', function () {
    [, $token] = cartUser('+905550003005');

    $this->withToken($token)
        ->postJson('/api/v1/cart/items', ['product_id' => 99999, 'quantity' => 1])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['product_id']);
});

// ── PUT /api/v1/cart/items/{item} ─────────────────────────────────────────────

test('user can update cart item quantity', function () {
    [$user, $token] = cartUser('+905550003006');
    $product        = Product::factory()->create();

    // Add item first
    $this->withToken($token)->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 2]);

    $cart = Cart::where('user_id', $user->id)->first();
    $item = CartItem::where('cart_id', $cart->id)->first();

    $this->withToken($token)
        ->putJson("/api/v1/cart/items/{$item->id}", ['quantity' => 7])
        ->assertStatus(200);

    expect(CartItem::find($item->id)->quantity)->toBe(7);
});

test('setting quantity to 0 removes the item', function () {
    [$user, $token] = cartUser('+905550003007');
    $product        = Product::factory()->create();

    $this->withToken($token)->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 3]);

    $cart = Cart::where('user_id', $user->id)->first();
    $item = CartItem::where('cart_id', $cart->id)->first();

    $this->withToken($token)
        ->putJson("/api/v1/cart/items/{$item->id}", ['quantity' => 0])
        ->assertStatus(200);

    $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
});

test('user cannot update another users cart item', function () {
    // Two separate users
    $user1 = User::factory()->create(['phone' => '+905550003008']);
    $user2 = User::factory()->create(['phone' => '+905550003009']);
    $product = Product::factory()->create();

    // User1 adds to cart  (using actingAs to set guard explicitly)
    $this->actingAs($user1, 'sanctum')
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 2]);

    $cart = Cart::where('user_id', $user1->id)->first();
    $item = CartItem::where('cart_id', $cart->id)->first();

    // User2 tries to update user1's item — must be 403
    // actingAs resets the guard cache between requests in the same test
    $this->actingAs($user2, 'sanctum')
        ->putJson("/api/v1/cart/items/{$item->id}", ['quantity' => 99])
        ->assertStatus(403);
});

// ── DELETE /api/v1/cart/items/{item} ─────────────────────────────────────────

test('user can remove a cart item', function () {
    [$user, $token] = cartUser('+905550003010');
    $product        = Product::factory()->create();

    $this->withToken($token)->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 5]);

    $cart = Cart::where('user_id', $user->id)->first();
    $item = CartItem::where('cart_id', $cart->id)->first();

    $this->withToken($token)
        ->deleteJson("/api/v1/cart/items/{$item->id}")
        ->assertStatus(200);

    $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
});

// ── DELETE /api/v1/cart ───────────────────────────────────────────────────────

test('user can clear entire cart', function () {
    [$user, $token] = cartUser('+905550003011');
    $p1             = Product::factory()->create();
    $p2             = Product::factory()->create();

    $this->withToken($token)->postJson('/api/v1/cart/items', ['product_id' => $p1->id, 'quantity' => 2]);
    $this->withToken($token)->postJson('/api/v1/cart/items', ['product_id' => $p2->id, 'quantity' => 3]);

    $response = $this->withToken($token)
        ->deleteJson('/api/v1/cart')
        ->assertStatus(200);

    expect($response->json('data.item_count'))->toBe(0);
});
