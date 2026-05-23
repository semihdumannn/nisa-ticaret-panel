<?php

namespace App\Modules\User\Domain\Exceptions;

use RuntimeException;

class InvalidFirebaseTokenException extends RuntimeException
{
    public function __construct(string $message = 'Invalid or expired Firebase token', int $code = 401)
    {
        parent::__construct($message, $code);
    }
}
