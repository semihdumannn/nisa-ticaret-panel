<?php

namespace App\Modules\Campaign\Domain\Exceptions;

use RuntimeException;

class InvalidCouponException extends RuntimeException
{
    public function __construct(string $message = 'Invalid or expired coupon code.')
    {
        parent::__construct($message, 422);
    }
}
