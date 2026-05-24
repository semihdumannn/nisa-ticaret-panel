<?php

namespace App\Modules\Order\Domain\ValueObjects;

enum OrderStatus: string
{
    case PENDING    = 'pending';
    case CONFIRMED  = 'confirmed';
    case PREPARING  = 'preparing';
    case ON_THE_WAY = 'on_the_way';
    case DELIVERED  = 'delivered';
    case CANCELLED  = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING    => 'Pending',
            self::CONFIRMED  => 'Confirmed',
            self::PREPARING  => 'Preparing',
            self::ON_THE_WAY => 'On the Way',
            self::DELIVERED  => 'Delivered',
            self::CANCELLED  => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING    => 'gray',
            self::CONFIRMED  => 'info',
            self::PREPARING  => 'warning',
            self::ON_THE_WAY => 'primary',
            self::DELIVERED  => 'success',
            self::CANCELLED  => 'danger',
        };
    }

    /** Returns the statuses this status can legally transition to. */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING    => [self::CONFIRMED,  self::CANCELLED],
            self::CONFIRMED  => [self::PREPARING,  self::CANCELLED],
            self::PREPARING  => [self::ON_THE_WAY, self::CANCELLED],
            self::ON_THE_WAY => [self::DELIVERED,  self::CANCELLED],
            self::DELIVERED  => [],
            self::CANCELLED  => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), strict: true);
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::DELIVERED, self::CANCELLED => true,
            default => false,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $s) => [$s->value => $s->label()])
            ->all();
    }
}
