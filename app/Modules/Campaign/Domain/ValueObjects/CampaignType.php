<?php

namespace App\Modules\Campaign\Domain\ValueObjects;

enum CampaignType: string
{
    case PERCENTAGE   = 'percentage';
    case FIXED_AMOUNT = 'fixed_amount';
    case BUY_X_GET_Y  = 'buy_x_get_y';

    public function label(): string
    {
        return match ($this) {
            self::PERCENTAGE   => 'Percentage',
            self::FIXED_AMOUNT => 'Fixed Amount',
            self::BUY_X_GET_Y  => 'Buy X Get Y',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PERCENTAGE   => 'success',
            self::FIXED_AMOUNT => 'info',
            self::BUY_X_GET_Y  => 'warning',
        };
    }
}
