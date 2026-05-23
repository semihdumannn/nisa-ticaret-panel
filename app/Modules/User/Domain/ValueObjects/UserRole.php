<?php

namespace App\Modules\User\Domain\ValueObjects;

enum UserRole: string
{
    case CUSTOMER    = 'customer';
    case FIELD_AGENT = 'field_agent';
    case DELIVERY    = 'delivery';
    case ADMIN       = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::CUSTOMER    => 'Customer',
            self::FIELD_AGENT => 'Field Agent',
            self::DELIVERY    => 'Delivery',
            self::ADMIN       => 'Admin',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
