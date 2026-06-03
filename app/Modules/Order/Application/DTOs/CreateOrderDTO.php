<?php

namespace App\Modules\Order\Application\DTOs;

readonly class CreateOrderDTO
{
    public function __construct(
        public int     $userId,
        public int     $addressId,
        public ?string $paymentMethod = null,
        public ?string $notes         = null,
        public ?string $couponCode    = null,
        public array   $items         = [],
    ) {}
}
