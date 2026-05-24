<?php

namespace App\Modules\Order\Domain\Exceptions;

use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use RuntimeException;

class InvalidOrderTransitionException extends RuntimeException
{
    public function __construct(
        public readonly OrderStatus $from,
        public readonly OrderStatus $to,
    ) {
        parent::__construct(
            "Cannot transition order from '{$from->value}' to '{$to->value}'.",
            422,
        );
    }
}
