<?php

namespace App\Modules\Product\Domain\ValueObjects;

enum ProductUnit: string
{
    case PIECE  = 'piece';
    case KG     = 'kg';
    case LITER  = 'liter';
    case BOX    = 'box';
    case PACK   = 'pack';

    public function label(): string
    {
        return match ($this) {
            self::PIECE => 'Piece',
            self::KG    => 'Kilogram',
            self::LITER => 'Liter',
            self::BOX   => 'Box',
            self::PACK  => 'Pack',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn (self $u) => [$u->value => $u->label()],
        )->all();
    }
}
