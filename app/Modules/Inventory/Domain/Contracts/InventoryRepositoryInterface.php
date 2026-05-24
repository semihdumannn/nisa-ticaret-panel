<?php

namespace App\Modules\Inventory\Domain\Contracts;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InventoryRepositoryInterface
{
    /** Find (or create) an inventory row for product+variant+warehouse. */
    public function findOrCreate(int $productId, ?int $variantId, int $warehouseId): Inventory;

    /** Stock levels for every warehouse that holds a given product. */
    public function forProduct(int $productId): Collection;

    /** All inventory rows in a warehouse (optionally filtered by product). */
    public function forWarehouse(int $warehouseId, ?int $productId = null): Collection;

    /** Products with available stock ≤ threshold. */
    public function lowStock(int $threshold = 5): Collection;

    /** Products with zero available stock. */
    public function outOfStock(): Collection;

    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator;
}
