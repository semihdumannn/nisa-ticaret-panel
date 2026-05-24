<?php

namespace App\Modules\Campaign\Domain\ValueObjects;

enum CouponType: string
{
    case PERCENTAGE   = 'percentage';
    case FIXED_AMOUNT = 'fixed_amount';

    public function label(): string
    {
        return match ($this) {
            self::PERCENTAGE   => 'Percentage',
            self::FIXED_AMOUNT => 'Fixed Amount',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PERCENTAGE   => 'success',
            self::FIXED_AMOUNT => 'info',
        };
    }
}
