<?php

namespace App\Modules\User\Domain\Exceptions;

use RuntimeException;

class InvalidTotpException extends RuntimeException
{
    public function __construct(string $message = 'Invalid or expired TOTP code', int $code = 401)
    {
        parent::__construct($message, $code);
    }
}
