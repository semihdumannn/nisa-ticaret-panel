<?php

namespace App\Modules\Product\Domain\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function findById(int $id, array $with = []): ?Product;
    public function findBySlug(string $slug, array $with = []): ?Product;
    public function findBySku(string $sku): ?Product;

    /**
     * Paginate products with optional filters.
     *
     * @param array{
     *   category_id?: int,
     *   brand_id?: int,
     *   min_price?: float,
     *   max_price?: float,
     *   is_featured?: bool,
     *   search?: string,
     * } $filters
     */
    public function paginate(int $perPage = 15, array $filters = [], string $sort = 'created_at', string $direction = 'desc'): LengthAwarePaginator;

    public function create(array $data): Product;
    public function update(Product $product, array $data): Product;
    public function delete(Product $product): void;
}
