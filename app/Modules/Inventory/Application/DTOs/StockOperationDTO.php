<?php

namespace App\Modules\Inventory\Application\DTOs;

readonly class StockOperationDTO
{
    public function __construct(
        public int     $productId,
        public int     $warehouseId,
        public int     $quantity,
        public ?string $reason       = null,
        public ?int    $variantId    = null,
        public ?string $referenceType = null,
        public ?int    $referenceId  = null,
        public ?int    $userId       = null,
    ) {}
}
