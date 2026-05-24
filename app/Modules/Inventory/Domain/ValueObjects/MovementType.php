<?php

namespace App\Modules\Inventory\Domain\ValueObjects;

enum MovementType: string
{
    case IN         = 'in';
    case OUT        = 'out';
    case TRANSFER   = 'transfer';
    case ADJUSTMENT = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::IN         => 'Stock In',
            self::OUT        => 'Stock Out',
            self::TRANSFER   => 'Transfer',
            self::ADJUSTMENT => 'Adjustment',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::IN         => 'success',
            self::OUT        => 'danger',
            self::TRANSFER   => 'warning',
            self::ADJUSTMENT => 'info',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $t) => [$t->value => $t->label()])
            ->all();
    }
}
