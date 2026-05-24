<?php

namespace App\Modules\Inventory\Application\DTOs;

readonly class TransferStockDTO
{
    public function __construct(
        public int     $productId,
        public int     $fromWarehouseId,
        public int     $toWarehouseId,
        public int     $quantity,
        public ?string $reason    = null,
        public ?int    $variantId = null,
        public ?int    $userId    = null,
    ) {}
}
