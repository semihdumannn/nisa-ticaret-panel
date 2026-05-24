<?php

namespace App\Modules\Order\Domain\ValueObjects;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID    = 'paid';
    case FAILED  = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PAID    => 'Paid',
            self::FAILED  => 'Failed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PAID    => 'success',
            self::FAILED  => 'danger',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $s) => [$s->value => $s->label()])
            ->all();
    }
}
