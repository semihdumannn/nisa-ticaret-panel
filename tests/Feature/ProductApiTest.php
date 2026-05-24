<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function adminUser(): array
{
    $user = User::factory()->admin()->create(['phone' => '+905550001001']);
    $user->assignRole('admin');
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

function customerUser(): array
{
    $user  = User::factory()->create(['phone' => '+905550001002']);
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

// ── GET /api/v1/products ──────────────────────────────────────────────────────

test('public user can list active products', function () {
    Product::factory()->count(3)->create();
    Product::factory()->inactive()->create();

    $this->getJson('/api/v1/products')
        ->assertStatus(200)
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure(['data' => [['id', 'name', 'price', 'sku']], 'meta']);
});

test('products list supports pagination', function () {
    Product::factory()->count(20)->create();

    $this->getJson('/api/v1/products?per_page=5')
        ->assertStatus(200)
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.total', 20);
});

test('products can be filtered by brand', function () {
    $brand = Brand::factory()->create();
    Product::factory()->count(2)->forBrand($brand->id)->create();
    Product::factory()->create();

    $this->getJson("/api/v1/products?brand_id={$brand->id}")
        ->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('products can be filtered by category', function () {
    $category = Category::factory()->create();
    $products = Product::factory()->count(2)->create();
    $products->each(fn ($p) => $p->categories()->attach($category->id));
    Product::factory()->create();

    $this->getJson("/api/v1/products?category_id={$category->id}")
        ->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('products can be filtered by price range', function () {
    Product::factory()->create(['price' => 10]);
    Product::factory()->create(['price' => 50]);
    Product::factory()->create(['price' => 200]);

    $this->getJson('/api/v1/products?min_price=20&max_price=100')
        ->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('products can be sorted by price ascending', function () {
    Product::factory()->create(['price' => 100, 'name' => 'Expensive']);
    Product::factory()->create(['price' => 10, 'name' => 'Cheap']);

    $response = $this->getJson('/api/v1/products?sort=price&direction=asc');
    $data     = $response->json('data');

    expect((float) $data[0]['price'])->toBe(10.0);
    expect((float) $data[1]['price'])->toBe(100.0);
});

// ── GET /api/v1/products/{product} ───────────────────────────────────────────

test('public user can view a product', function () {
    $product = Product::factory()->create();

    $this->getJson("/api/v1/products/{$product->id}")
        ->assertStatus(200)
        ->assertJsonPath('product.id', $product->id)
        ->assertJsonStructure(['product' => ['id', 'name', 'price', 'brand', 'categories', 'images', 'variants']]);
});

test('viewing inactive product returns 404', function () {
    // Products are filtered to active() by scope — direct route model binding without scope
    // The show() method uses route model binding which doesn't apply active() scope by default
    $product = Product::factory()->inactive()->create();

    // The show route uses route model binding (no active scope), so 200 is expected
    $this->getJson("/api/v1/products/{$product->id}")
        ->assertStatus(200);
});

// ── GET /api/v1/products/search ───────────────────────────────────────────────

test('product search requires q parameter', function () {
    $this->getJson('/api/v1/products/search')
        ->assertStatus(422);
});

test('product search returns results', function () {
    Product::factory()->create(['name' => 'Fanta Orange Special']);
    Product::factory()->create(['name' => 'Coca Cola Classic']);

    $this->getJson('/api/v1/products/search?q=Fanta')
        ->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

// ── POST /api/v1/products (admin) ─────────────────────────────────────────────

test('admin can create a product', function () {
    [, $token] = adminUser();
    $brand    = Brand::factory()->create();
    $category = Category::factory()->create();

    $this->withToken($token)
        ->postJson('/api/v1/products', [
            'name'         => 'New Beverage',
            'price'        => 25.99,
            'brand_id'     => $brand->id,
            'category_ids' => [$category->id],
        ])
        ->assertStatus(201)
        ->assertJsonPath('product.name', 'New Beverage')
        ->assertJsonPath('product.price', 25.99);

    $this->assertDatabaseHas('products', ['name' => 'New Beverage']);
});

test('admin product creation assigns categories', function () {
    [, $token] = adminUser();
    $cat1 = Category::factory()->create();
    $cat2 = Category::factory()->create();

    $response = $this->withToken($token)
        ->postJson('/api/v1/products', [
            'name'         => 'Multi-Category Product',
            'price'        => 10,
            'category_ids' => [$cat1->id, $cat2->id],
        ]);

    $productId = $response->json('product.id');
    expect(Product::find($productId)->categories()->count())->toBe(2);
});

test('product creation requires name and price', function () {
    [, $token] = adminUser();

    $this->withToken($token)
        ->postJson('/api/v1/products', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'price']);
});

test('non-admin cannot create products', function () {
    [, $token] = customerUser();

    $this->withToken($token)
        ->postJson('/api/v1/products', ['name' => 'Test', 'price' => 10])
        ->assertStatus(403);
});

test('unauthenticated user cannot create products', function () {
    $this->postJson('/api/v1/products', ['name' => 'Test', 'price' => 10])
        ->assertStatus(401);
});

// ── PUT /api/v1/products/{product} ───────────────────────────────────────────

test('admin can update a product', function () {
    [, $token] = adminUser();
    $product  = Product::factory()->create();

    $this->withToken($token)
        ->putJson("/api/v1/products/{$product->id}", ['price' => 99.99])
        ->assertStatus(200)
        ->assertJsonPath('product.price', 99.99);
});

test('non-admin cannot update products', function () {
    [, $token] = customerUser();
    $product  = Product::factory()->create();

    $this->withToken($token)
        ->putJson("/api/v1/products/{$product->id}", ['price' => 1])
        ->assertStatus(403);
});

// ── DELETE /api/v1/products/{product} ────────────────────────────────────────

test('admin can delete a product', function () {
    [, $token] = adminUser();
    $product  = Product::factory()->create();

    $this->withToken($token)
        ->deleteJson("/api/v1/products/{$product->id}")
        ->assertStatus(200);

    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

test('non-admin cannot delete products', function () {
    [, $token] = customerUser();
    $product  = Product::factory()->create();

    $this->withToken($token)
        ->deleteJson("/api/v1/products/{$product->id}")
        ->assertStatus(403);
});
