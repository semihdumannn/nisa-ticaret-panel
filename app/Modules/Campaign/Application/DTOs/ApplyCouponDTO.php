<?php

namespace App\Modules\Campaign\Application\DTOs;

readonly class ApplyCouponDTO
{
    public function __construct(
        public string $code,
        public int    $userId,
        public float  $subtotal,
    ) {}
}
