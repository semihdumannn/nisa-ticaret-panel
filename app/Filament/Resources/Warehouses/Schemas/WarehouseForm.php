<?php

namespace App\Filament\Resources\Warehouses\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Warehouse Details')->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(100),
                TextInput::make('code')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true)
                    ->placeholder('e.g. WH-IST-01'),
                TextInput::make('city')->maxLength(100),
                Toggle::make('is_active')->default(true)->onColor('success'),
                Textarea::make('address')->columnSpanFull()->rows(2),
            ]),
        ]);
    }
}
