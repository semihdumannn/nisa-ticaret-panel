<?php

namespace App\Modules\Review\Domain\Exceptions;

use RuntimeException;

class ReviewNotAllowedException extends RuntimeException
{
    public function __construct(public readonly string $errorCode)
    {
        parent::__construct($errorCode, 422);
    }
}
