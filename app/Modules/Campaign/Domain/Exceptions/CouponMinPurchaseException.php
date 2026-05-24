<?php

namespace App\Modules\Campaign\Domain\Exceptions;

use RuntimeException;

class CouponMinPurchaseException extends RuntimeException
{
    public function __construct(public readonly float $minAmount)
    {
        parent::__construct(
            sprintf('Minimum purchase amount of %.2f is required to use this coupon.', $minAmount),
            422,
        );
    }
}
