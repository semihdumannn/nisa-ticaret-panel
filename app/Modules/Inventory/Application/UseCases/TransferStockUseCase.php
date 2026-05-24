<?php

namespace App\Modules\Inventory\Application\UseCases;

use App\Modules\Inventory\Application\DTOs\TransferStockDTO;
use App\Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;
use App\Modules\Inventory\Domain\Contracts\StockMovementRepositoryInterface;
use App\Modules\Inventory\Domain\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\DB;

class TransferStockUseCase
{
    public function __construct(
        private readonly InventoryRepositoryInterface     $inventoryRepo,
        private readonly StockMovementRepositoryInterface $movementRepo,
    ) {}

    /**
     * Move stock between two warehouses (type = 'transfer').
     * Single atomic transaction: deduct source, add destination, record movement.
     *
     * @throws InsufficientStockException
     */
    public function execute(TransferStockDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            $source = $this->inventoryRepo->findOrCreate(
                $dto->productId,
                $dto->variantId,
                $dto->fromWarehouseId,
            );

            if ($source->availableQuantity() < $dto->quantity) {
                throw new InsufficientStockException(
                    requested: $dto->quantity,
                    available: $source->availableQuantity(),
                );
            }

            $source->decrement('quantity', $dto->quantity);

            $destination = $this->inventoryRepo->findOrCreate(
                $dto->productId,
                $dto->variantId,
                $dto->toWarehouseId,
            );
            $destination->increment('quantity', $dto->quantity);

            $this->movementRepo->record([
                'product_id'     => $dto->productId,
                'variant_id'     => $dto->variantId,
                'warehouse_id'   => $dto->fromWarehouseId,
                'type'           => 'transfer',
                'quantity'       => -$dto->quantity,
                'reason'         => $dto->reason ?? "Transfer to warehouse #{$dto->toWarehouseId}",
                'user_id'        => $dto->userId,
            ]);

            $this->movementRepo->record([
                'product_id'     => $dto->productId,
                'variant_id'     => $dto->variantId,
                'warehouse_id'   => $dto->toWarehouseId,
                'type'           => 'transfer',
                'quantity'       => $dto->quantity,
                'reason'         => $dto->reason ?? "Transfer from warehouse #{$dto->fromWarehouseId}",
                'user_id'        => $dto->userId,
            ]);
        });
    }
}
