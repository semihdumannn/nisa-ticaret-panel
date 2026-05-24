<?php

namespace App\Modules\Inventory\Infrastructure\Repositories;

use App\Models\Inventory;
use App\Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentInventoryRepository implements InventoryRepositoryInterface
{
    public function findOrCreate(int $productId, ?int $variantId, int $warehouseId): Inventory
    {
        return Inventory::firstOrCreate(
            [
                'product_id'   => $productId,
                'variant_id'   => $variantId,
                'warehouse_id' => $warehouseId,
            ],
            [
                'quantity'          => 0,
                'reserved_quantity' => 0,
            ]
        );
    }

    public function forProduct(int $productId): Collection
    {
        return Inventory::with(['warehouse', 'variant'])
            ->where('product_id', $productId)
            ->get();
    }

    public function forWarehouse(int $warehouseId, ?int $productId = null): Collection
    {
        $query = Inventory::with(['product', 'variant'])
            ->where('warehouse_id', $warehouseId);

        if ($productId) {
            $query->where('product_id', $productId);
        }

        return $query->get();
    }

    public function lowStock(int $threshold = 5): Collection
    {
        return Inventory::with(['product', 'warehouse', 'variant'])
            ->lowStock($threshold)
            ->get();
    }

    public function outOfStock(): Collection
    {
        return Inventory::with(['product', 'warehouse', 'variant'])
            ->outOfStock()
            ->get();
    }

    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = Inventory::with(['product', 'warehouse', 'variant']);

        if (! empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (! empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $query->lowStock((int) ($filters['threshold'] ?? 5));
        }

        return $query->latest()->paginate($perPage);
    }
}
