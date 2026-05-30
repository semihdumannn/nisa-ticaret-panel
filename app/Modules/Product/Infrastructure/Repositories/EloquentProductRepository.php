<?php

namespace App\Modules\Product\Infrastructure\Repositories;

use App\Models\Category;
use App\Models\Product;
use App\Modules\Product\Domain\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProductRepository implements ProductRepositoryInterface
{
    private const ALLOWED_SORTS = ['name', 'price', 'created_at', 'is_featured'];

    public function findById(int $id, array $with = []): ?Product
    {
        return Product::with($with)->find($id);
    }

    public function findBySlug(string $slug, array $with = []): ?Product
    {
        return Product::with($with)->where('slug', $slug)->first();
    }

    public function findBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }

    public function paginate(int $perPage = 15, array $filters = [], string $sort = 'created_at', string $direction = 'desc'): LengthAwarePaginator
    {
        $sort      = in_array($sort, self::ALLOWED_SORTS) ? $sort : 'created_at';
        $direction = $direction === 'asc' ? 'asc' : 'desc';

        $query = Product::with(['brand', 'categories', 'images', 'variants' => fn ($q) => $q->select('product_variants.*')->where('is_active', true)->orderByRaw("CAST(COALESCE(attributes->>'package_qty', '1') AS INTEGER)")])
            ->withSum('inventories as total_quantity', 'quantity')
            ->withSum('inventories as total_reserved', 'reserved_quantity')
            ->active();

        // Category filter (includes descendants)
        if (! empty($filters['category_id'])) {
            $category = Category::find($filters['category_id']);
            if ($category) {
                $categoryIds = array_merge([$category->id], $category->descendantIds());
                $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
            }
        }

        // Brand filter
        if (! empty($filters['brand_id'])) {
            $query->forBrand((int) $filters['brand_id']);
        }

        // Price range filter
        if (isset($filters['min_price']) || isset($filters['max_price'])) {
            $query->priceBetween($filters['min_price'] ?? null, $filters['max_price'] ?? null);
        }

        // Featured filter
        if (isset($filters['is_featured']) && $filters['is_featured']) {
            $query->featured();
        }

        return $query->orderBy('sort_order')->orderBy($sort, $direction)->paginate($perPage);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
