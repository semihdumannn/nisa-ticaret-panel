<?php

namespace App\Modules\Campaign\Domain\Exceptions;

use RuntimeException;

class CouponUsageLimitException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('This coupon has reached its usage limit.', 422);
    }
}
