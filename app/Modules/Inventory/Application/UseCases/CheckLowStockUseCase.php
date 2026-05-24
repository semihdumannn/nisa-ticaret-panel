<?php

namespace App\Modules\Inventory\Application\UseCases;

use App\Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CheckLowStockUseCase
{
    public function __construct(private readonly InventoryRepositoryInterface $inventoryRepo) {}

    /** Return inventory rows at or below the given threshold. */
    public function lowStock(int $threshold = 5): Collection
    {
        return $this->inventoryRepo->lowStock($threshold);
    }

    /** Return inventory rows with zero available stock. */
    public function outOfStock(): Collection
    {
        return $this->inventoryRepo->outOfStock();
    }

    /** Grouped summary: total products low / out across all warehouses. */
    public function summary(int $threshold = 5): array
    {
        return [
            'low_stock_count'  => $this->inventoryRepo->lowStock($threshold)->count(),
            'out_of_stock_count' => $this->inventoryRepo->outOfStock()->count(),
            'threshold'        => $threshold,
        ];
    }
}
