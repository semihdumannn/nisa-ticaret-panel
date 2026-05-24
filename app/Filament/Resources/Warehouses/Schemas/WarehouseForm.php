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
        return $schema
            ->columns(3)
            ->components([
                // ── Warehouse Info (2/3) ──────────────────────────────────────
                Section::make('Warehouse Information')
                    ->description('Name, code, and physical location.')
                    ->icon('heroicon-o-building-office-2')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Warehouse Name')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('e.g. Istanbul Main Depot'),

                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->placeholder('e.g. WH-IST-01')
                            ->helperText('Unique identifier. Letters, numbers, dashes.'),

                        TextInput::make('city')
                            ->label('City')
                            ->maxLength(100)
                            ->placeholder('e.g. Istanbul')
                            ->columnSpanFull(),

                        Textarea::make('address')
                            ->label('Full Address')
                            ->rows(2)
                            ->columnSpanFull()
                            ->placeholder('Street address, district…'),
                    ]),

                // ── Status (1/3) ──────────────────────────────────────────────
                Section::make('Status')
                    ->description('Activate or deactivate this warehouse.')
                    ->icon('heroicon-o-check-circle')
                    ->columnSpan(1)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->onColor('success')
                            ->helperText('Inactive warehouses cannot receive stock.'),
                    ]),
            ]);
    }
}
