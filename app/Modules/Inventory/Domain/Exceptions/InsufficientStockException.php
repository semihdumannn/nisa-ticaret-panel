<?php

namespace App\Modules\Inventory\Domain\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly int $requested,
        public readonly int $available,
        string $message = ''
    ) {
        parent::__construct(
            $message ?: "Insufficient stock: requested {$requested}, available {$available}.",
            422
        );
    }
}
