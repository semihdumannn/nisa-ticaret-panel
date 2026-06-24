<?php

namespace App\Modules\Subscription\Domain\Exceptions;

use RuntimeException;

class SubscriptionException extends RuntimeException
{
    public function __construct(string $message, public readonly string $errorCode = '')
    {
        parent::__construct($message, 422);
    }
}
