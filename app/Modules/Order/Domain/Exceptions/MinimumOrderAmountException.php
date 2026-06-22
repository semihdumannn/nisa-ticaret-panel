<?php

namespace App\Modules\Order\Domain\Exceptions;

use RuntimeException;

class MinimumOrderAmountException extends RuntimeException
{
    public function __construct(
        private readonly float $minimum,
        private readonly float $actual,
    ) {
        parent::__construct(
            "Minimum sipariş tutarı ₺{$minimum}'dir. Sepetiniz: ₺{$actual}",
            422
        );
    }

    public function getMinimum(): float { return $this->minimum; }
    public function getActual(): float  { return $this->actual; }
}
