<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

// ── GET /api/v1/brands ────────────────────────────────────────────────────────

test('public user can list brands', function () {
    Brand::factory()->count(3)->create();
    Brand::factory()->inactive()->create();

    $this->getJson('/api/v1/brands')
        ->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('brands are cached for 24 hours', function () {
    Brand::factory()->count(2)->create();

    // First call — populates cache
    $this->getJson('/api/v1/brands')->assertStatus(200);

    // Verify cache key exists
    expect(Cache::has('brands.all'))->toBeTrue();
});

test('brand list includes product count', function () {
    $brand   = Brand::factory()->create();
    $product = Product::factory()->forBrand($brand->id)->create();

    Cache::forget('brands.all');

    $response = $this->getJson('/api/v1/brands');
    $brandData = collect($response->json('data'))->firstWhere('id', $brand->id);
    expect($brandData['products_count'])->toBe(1);
});

// ── GET /api/v1/brands/{brand}/products ──────────────────────────────────────

test('can list products for a specific brand', function () {
    $brand = Brand::factory()->create();
    Product::factory()->count(3)->forBrand($brand->id)->create();
    Product::factory()->create();

    $this->getJson("/api/v1/brands/{$brand->id}/products")
        ->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

// ── GET /api/v1/categories ────────────────────────────────────────────────────

test('public user can list categories as tree', function () {
    $root  = Category::factory()->create(['is_active' => true]);
    $child = Category::factory()->child($root)->create(['is_active' => true]);
    Category::factory()->inactive()->create();

    Cache::forget('categories.tree');

    $response = $this->getJson('/api/v1/categories');

    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data)->toHaveCount(1); // one root
    expect($data[0]['children'])->toHaveCount(1); // one child
});

test('categories are cached for 24 hours', function () {
    Category::factory()->create();

    Cache::forget('categories.tree');
    $this->getJson('/api/v1/categories')->assertStatus(200);

    expect(Cache::has('categories.tree'))->toBeTrue();
});

// ── GET /api/v1/categories/{category}/products ───────────────────────────────

test('can list products for a specific category', function () {
    $category = Category::factory()->create();
    $products = Product::factory()->count(2)->create();
    $products->each(fn ($p) => $p->categories()->attach($category->id));
    Product::factory()->create(); // different category

    $this->getJson("/api/v1/categories/{$category->id}/products")
        ->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('category products includes products from descendant categories', function () {
    $parent   = Category::factory()->create(['name' => 'Parent']);
    $child    = Category::factory()->child($parent)->create(['name' => 'Child']);

    $productInParent = Product::factory()->create(['name' => 'Parent Product']);
    $productInChild  = Product::factory()->create(['name' => 'Child Product']);

    $productInParent->categories()->attach($parent->id);
    $productInChild->categories()->attach($child->id);

    // Request parent category products — should include child products
    $response = $this->getJson("/api/v1/categories/{$parent->id}/products");
    $response->assertStatus(200);
    expect($response->json('meta.total'))->toBe(2);
});
