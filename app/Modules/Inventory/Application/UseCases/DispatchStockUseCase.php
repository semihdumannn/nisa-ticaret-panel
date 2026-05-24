<?php

namespace App\Modules\Inventory\Application\UseCases;

use App\Models\Inventory;
use App\Modules\Inventory\Application\DTOs\StockOperationDTO;
use App\Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;
use App\Modules\Inventory\Domain\Contracts\StockMovementRepositoryInterface;
use App\Modules\Inventory\Domain\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\DB;

class DispatchStockUseCase
{
    public function __construct(
        private readonly InventoryRepositoryInterface     $inventoryRepo,
        private readonly StockMovementRepositoryInterface $movementRepo,
    ) {}

    /**
     * Remove stock from a warehouse (type = 'out').
     *
     * @throws InsufficientStockException
     */
    public function execute(StockOperationDTO $dto): Inventory
    {
        return DB::transaction(function () use ($dto) {
            $inventory = $this->inventoryRepo->findOrCreate(
                $dto->productId,
                $dto->variantId,
                $dto->warehouseId,
            );

            if ($inventory->availableQuantity() < $dto->quantity) {
                throw new InsufficientStockException(
                    requested: $dto->quantity,
                    available: $inventory->availableQuantity(),
                );
            }

            $inventory->decrement('quantity', $dto->quantity);
            $inventory->refresh();

            $this->movementRepo->record([
                'product_id'     => $dto->productId,
                'variant_id'     => $dto->variantId,
                'warehouse_id'   => $dto->warehouseId,
                'type'           => 'out',
                'quantity'       => $dto->quantity,
                'reason'         => $dto->reason ?? 'Stock dispatched',
                'reference_type' => $dto->referenceType,
                'reference_id'   => $dto->referenceId,
                'user_id'        => $dto->userId,
            ]);

            return $inventory;
        });
    }
}
