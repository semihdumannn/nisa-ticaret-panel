<?php

namespace App\Modules\Inventory\Application\UseCases;

use App\Models\Inventory;
use App\Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;
use App\Modules\Inventory\Domain\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\DB;

class ReserveStockUseCase
{
    public function __construct(private readonly InventoryRepositoryInterface $inventoryRepo) {}

    /**
     * Reserve stock for an order (increments reserved_quantity).
     *
     * @throws InsufficientStockException
     */
    public function reserve(int $productId, ?int $variantId, int $warehouseId, int $quantity): Inventory
    {
        return DB::transaction(function () use ($productId, $variantId, $warehouseId, $quantity) {
            $inventory = $this->inventoryRepo->findOrCreate($productId, $variantId, $warehouseId);

            if ($inventory->availableQuantity() < $quantity) {
                throw new InsufficientStockException(
                    requested: $quantity,
                    available: $inventory->availableQuantity(),
                );
            }

            $inventory->increment('reserved_quantity', $quantity);
            return $inventory->fresh();
        });
    }

    /**
     * Release a previously made reservation (decrements reserved_quantity).
     */
    public function release(int $productId, ?int $variantId, int $warehouseId, int $quantity): Inventory
    {
        return DB::transaction(function () use ($productId, $variantId, $warehouseId, $quantity) {
            $inventory = $this->inventoryRepo->findOrCreate($productId, $variantId, $warehouseId);

            $safeQty = min($quantity, $inventory->reserved_quantity);
            if ($safeQty > 0) {
                $inventory->decrement('reserved_quantity', $safeQty);
            }

            return $inventory->fresh();
        });
    }
}
