<?php

use App\Models\Favorite;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function favoriteUser(): User
{
    return User::factory()->create(['role' => 'customer', 'is_active' => true]);
}

test('user can list favorites', function () {
    $user    = favoriteUser();
    $product = Product::factory()->create();
    Favorite::create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/favorites')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta']);
});

test('user can add a favorite', function () {
    $user    = favoriteUser();
    $product = Product::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/favorites', ['product_id' => $product->id])
        ->assertCreated()
        ->assertJsonPath('product_id', $product->id);
});

test('adding duplicate favorite returns 409', function () {
    $user    = favoriteUser();
    $product = Product::factory()->create();
    Favorite::create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/favorites', ['product_id' => $product->id])
        ->assertStatus(409)
        ->assertJsonPath('error', 'ALREADY_FAVORITED');
});

test('user can remove favorite by id', function () {
    $user    = favoriteUser();
    $product = Product::factory()->create();
    $fav     = Favorite::create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/favorites/{$fav->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('favorites', ['id' => $fav->id]);
});

test('user can remove favorite by product id', function () {
    $user    = favoriteUser();
    $product = Product::factory()->create();
    Favorite::create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/favorites/by-product/{$product->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('favorites', ['user_id' => $user->id, 'product_id' => $product->id]);
});

test('user cannot delete another users favorite', function () {
    $user1   = favoriteUser();
    $user2   = favoriteUser();
    $product = Product::factory()->create();
    $fav     = Favorite::create(['user_id' => $user1->id, 'product_id' => $product->id]);

    $this->actingAs($user2, 'sanctum')
        ->deleteJson("/api/v1/favorites/{$fav->id}")
        ->assertNotFound();
});

test('product list includes is_favorited field', function () {
    $user    = favoriteUser();
    $product = Product::factory()->create(['is_active' => true]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/products')
        ->assertOk()
        ->assertJsonStructure(['data' => [['is_favorited']]]);
});

test('product list shows is_favorited true for favorited product', function () {
    $user    = favoriteUser();
    $product = Product::factory()->create(['is_active' => true]);
    Favorite::create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/products')
        ->assertOk()
        ->assertJsonFragment(['is_favorited' => true]);
});

test('product detail shows is_favorited true for favorited product', function () {
    $user    = favoriteUser();
    $product = Product::factory()->create(['is_active' => true]);
    Favorite::create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/products/{$product->id}")
        ->assertOk()
        ->assertJsonPath('product.is_favorited', true);
});
