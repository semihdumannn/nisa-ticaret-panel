<?php

namespace App\Modules\Order\Application\DTOs;

readonly class AddCartItemDTO
{
    public function __construct(
        public int  $productId,
        public int  $quantity,
        public ?int $variantId = null,
    ) {}
}
