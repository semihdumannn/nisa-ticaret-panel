<?php

namespace App\Modules\Inventory\Application\UseCases;

use App\Models\Inventory;
use App\Modules\Inventory\Application\DTOs\StockOperationDTO;
use App\Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;
use App\Modules\Inventory\Domain\Contracts\StockMovementRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ReceiveStockUseCase
{
    public function __construct(
        private readonly InventoryRepositoryInterface     $inventoryRepo,
        private readonly StockMovementRepositoryInterface $movementRepo,
    ) {}

    /**
     * Add stock to a warehouse (type = 'in').
     * Atomic: inventory update + movement record in one transaction.
     */
    public function execute(StockOperationDTO $dto): Inventory
    {
        return DB::transaction(function () use ($dto) {
            $inventory = $this->inventoryRepo->findOrCreate(
                $dto->productId,
                $dto->variantId,
                $dto->warehouseId,
            );

            $inventory->increment('quantity', $dto->quantity);
            $inventory->update(['last_restock_date' => now()]);
            $inventory->refresh();

            $this->movementRepo->record([
                'product_id'     => $dto->productId,
                'variant_id'     => $dto->variantId,
                'warehouse_id'   => $dto->warehouseId,
                'type'           => 'in',
                'quantity'       => $dto->quantity,
                'reason'         => $dto->reason ?? 'Stock received',
                'reference_type' => $dto->referenceType,
                'reference_id'   => $dto->referenceId,
                'user_id'        => $dto->userId,
            ]);

            return $inventory;
        });
    }
}
