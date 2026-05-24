<?php

namespace App\Modules\Notification\Domain\ValueObjects;

enum NotificationType: string
{
    case ORDER_UPDATE = 'order_update';
    case PROMOTION    = 'promotion';
    case SYSTEM       = 'system';

    public function label(): string
    {
        return match ($this) {
            self::ORDER_UPDATE => 'Order Update',
            self::PROMOTION    => 'Promotion',
            self::SYSTEM       => 'System',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ORDER_UPDATE => 'info',
            self::PROMOTION    => 'success',
            self::SYSTEM       => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ORDER_UPDATE => 'heroicon-o-shopping-bag',
            self::PROMOTION    => 'heroicon-o-tag',
            self::SYSTEM       => 'heroicon-o-bell',
        };
    }
}
