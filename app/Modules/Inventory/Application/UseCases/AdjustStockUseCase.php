<?php

namespace App\Modules\Inventory\Application\UseCases;

use App\Models\Inventory;
use App\Modules\Inventory\Application\DTOs\StockOperationDTO;
use App\Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;
use App\Modules\Inventory\Domain\Contracts\StockMovementRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AdjustStockUseCase
{
    public function __construct(
        private readonly InventoryRepositoryInterface     $inventoryRepo,
        private readonly StockMovementRepositoryInterface $movementRepo,
    ) {}

    /**
     * Set inventory to an absolute quantity (type = 'adjustment').
     * The recorded movement quantity is the delta (positive or negative).
     */
    public function execute(StockOperationDTO $dto): Inventory
    {
        return DB::transaction(function () use ($dto) {
            $inventory = $this->inventoryRepo->findOrCreate(
                $dto->productId,
                $dto->variantId,
                $dto->warehouseId,
            );

            $delta = $dto->quantity - $inventory->quantity;

            $inventory->update(['quantity' => $dto->quantity]);
            $inventory->refresh();

            $this->movementRepo->record([
                'product_id'     => $dto->productId,
                'variant_id'     => $dto->variantId,
                'warehouse_id'   => $dto->warehouseId,
                'type'           => 'adjustment',
                'quantity'       => $delta,        // signed delta
                'reason'         => $dto->reason ?? 'Manual adjustment',
                'reference_type' => $dto->referenceType,
                'reference_id'   => $dto->referenceId,
                'user_id'        => $dto->userId,
            ]);

            return $inventory;
        });
    }
}
