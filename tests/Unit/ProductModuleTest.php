<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Modules\Product\Application\DTOs\CreateProductDTO;
use App\Modules\Product\Application\UseCases\CreateProductUseCase;
use App\Modules\Product\Domain\ValueObjects\ProductUnit;
use App\Modules\Product\Infrastructure\Repositories\EloquentBrandRepository;
use App\Modules\Product\Infrastructure\Repositories\EloquentCategoryRepository;
use App\Modules\Product\Infrastructure\Repositories\EloquentProductRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── ProductUnit Enum ──────────────────────────────────────────────────────────

test('ProductUnit has correct values', function () {
    expect(ProductUnit::PIECE->value)->toBe('piece');
    expect(ProductUnit::KG->value)->toBe('kg');
    expect(ProductUnit::LITER->value)->toBe('liter');
});

test('ProductUnit options returns associative array', function () {
    $options = ProductUnit::options();
    expect($options)->toHaveKey('piece');
    expect($options['piece'])->toBe('Piece');
});

// ── Brand Model ───────────────────────────────────────────────────────────────

test('Brand auto-generates slug on create', function () {
    $brand = Brand::factory()->create(['name' => 'Coca-Cola Türkiye']);
    expect($brand->slug)->toBe('coca-cola-turkiye');
});

test('Brand generates unique slug when slug already taken', function () {
    // "Pepsi" and "Pepsi!" both produce slug "pepsi" — names are different (no unique violation)
    Brand::factory()->create(['name' => 'Pepsi']);
    $brand2 = Brand::factory()->create(['name' => 'Pepsi!']);
    expect($brand2->slug)->toBe('pepsi-1');
});

test('Brand scopeActive filters inactive brands', function () {
    Brand::factory()->create(['is_active' => true]);
    Brand::factory()->inactive()->create();
    expect(Brand::active()->count())->toBe(1);
});

test('Brand has products relationship', function () {
    $brand   = Brand::factory()->create();
    $product = Product::factory()->forBrand($brand->id)->create();
    expect($brand->products()->count())->toBe(1);
});

// ── Category Model ────────────────────────────────────────────────────────────

test('Category auto-generates slug on create', function () {
    $cat = Category::factory()->create(['name' => 'Soğuk İçecekler']);
    expect($cat->slug)->not->toBeEmpty();
    expect($cat->slug)->toContain('soguk');
});

test('Category supports nested parent-child', function () {
    $parent = Category::factory()->create();
    $child  = Category::factory()->child($parent)->create();

    expect($child->parent->id)->toBe($parent->id);
    expect($parent->children()->count())->toBe(1);
});

test('Category scopeRoot returns top-level only', function () {
    $parent = Category::factory()->create();
    Category::factory()->child($parent)->create();

    expect(Category::root()->count())->toBe(1);
});

test('Category descendantIds returns all descendants', function () {
    $root  = Category::factory()->create(['name' => 'Root']);
    $child = Category::factory()->child($root)->create(['name' => 'Child']);
    $grand = Category::factory()->create(['parent_id' => $child->id, 'name' => 'Grand']);

    $ids = $root->descendantIds();
    expect($ids)->toContain($child->id, $grand->id);
});

// ── Product Model ─────────────────────────────────────────────────────────────

test('Product auto-generates slug and SKU on create', function () {
    $product = Product::factory()->create(['name' => 'Test Ürün']);
    expect($product->slug)->toBe('test-urun');
    expect($product->sku)->toStartWith('SKU-');
});

test('Product priceWithTax calculates correctly', function () {
    $product = Product::factory()->create(['price' => 100, 'tax_rate' => 20]);
    expect($product->priceWithTax())->toBe(120.0);
});

test('Product marginPercent calculates correctly', function () {
    $product = Product::factory()->create(['price' => 100, 'cost_price' => 60]);
    expect($product->marginPercent())->toBe(40.0);
});

test('Product marginPercent returns null when cost price not set', function () {
    $product = Product::factory()->create(['price' => 100, 'cost_price' => null]);
    expect($product->marginPercent())->toBeNull();
});

test('Product belongs to brand', function () {
    $brand   = Brand::factory()->create();
    $product = Product::factory()->forBrand($brand->id)->create();
    expect($product->brand->id)->toBe($brand->id);
});

test('Product has many categories via pivot', function () {
    $product    = Product::factory()->create();
    $categories = Category::factory()->count(2)->create();
    $product->categories()->sync($categories->pluck('id')->toArray());

    expect($product->categories()->count())->toBe(2);
});

test('Product scopeActive filters correctly', function () {
    Product::factory()->create(['is_active' => true]);
    Product::factory()->inactive()->create();
    expect(Product::active()->count())->toBe(1);
});

// ── ProductVariant Model ──────────────────────────────────────────────────────

test('ProductVariant effectivePrice sums base and adjustment', function () {
    $product = Product::factory()->create(['price' => 100]);
    $variant = $product->variants()->create([
        'sku'              => 'VAR-001',
        'name'             => '500ml',
        'attributes'       => ['size' => '500ml'],
        'price_adjustment' => 15,
        'stock'            => 10,
        'is_active'        => true,
    ]);

    expect($variant->effectivePrice())->toBe(115.0);
});

// ── Repositories ──────────────────────────────────────────────────────────────

test('EloquentBrandRepository finds brand by slug', function () {
    $brand = Brand::factory()->create(['name' => 'FindMe Brand']);
    $repo  = app(EloquentBrandRepository::class);

    expect($repo->findBySlug($brand->slug)?->id)->toBe($brand->id);
    expect($repo->findBySlug('nonexistent'))->toBeNull();
});

test('EloquentCategoryRepository tree returns root with children', function () {
    $root  = Category::factory()->create(['name' => 'Root A', 'is_active' => true]);
    $child = Category::factory()->child($root)->create(['name' => 'Child A', 'is_active' => true]);

    $repo = app(EloquentCategoryRepository::class);
    $tree = $repo->tree();

    expect($tree)->toHaveCount(1);
    expect($tree->first()->childrenRecursive)->toHaveCount(1);
});

test('EloquentProductRepository paginates with brand filter', function () {
    $brand    = Brand::factory()->create();
    $products = Product::factory()->count(3)->forBrand($brand->id)->create();
    Product::factory()->create(); // another brand

    $repo   = app(EloquentProductRepository::class);
    $result = $repo->paginate(15, ['brand_id' => $brand->id]);

    expect($result->total())->toBe(3);
});

// ── CreateProductUseCase ──────────────────────────────────────────────────────

test('CreateProductUseCase creates product with categories', function () {
    $cat1 = Category::factory()->create();
    $cat2 = Category::factory()->create();

    $useCase = app(CreateProductUseCase::class);
    $product = $useCase->execute(new CreateProductDTO(
        name:        'Fanta Orange 330ml',
        price:       12.5,
        categoryIds: [$cat1->id, $cat2->id],
    ));

    expect($product)->toBeInstanceOf(Product::class);
    expect($product->categories()->count())->toBe(2);
    expect($product->name)->toBe('Fanta Orange 330ml');
});
