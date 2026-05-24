<?php

namespace App\Filament\Resources\Inventories\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                // ── Product & Location ────────────────────────────────────────
                Section::make('Product & Location')
                    ->description('Which product and warehouse this inventory record tracks.')
                    ->icon('heroicon-o-building-storefront')
                    ->columns(2)
                    ->columnSpan(1)
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'name')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->preload(),

                        Select::make('variant_id')
                            ->label('Variant')
                            ->relationship('variant', 'name')
                            ->native(false)
                            ->searchable()
                            ->nullable()
                            ->placeholder('No variant (base product)'),

                        Select::make('warehouse_id')
                            ->label('Warehouse')
                            ->relationship('warehouse', 'name')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ]),

                // ── Stock Levels ──────────────────────────────────────────────
                Section::make('Stock Levels')
                    ->description('Current quantities on hand and reserved for pending orders.')
                    ->icon('heroicon-o-archive-box')
                    ->columns(2)
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('quantity')
                            ->label('Total Quantity')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('units')
                            ->helperText('Physical stock on hand.'),

                        TextInput::make('reserved_quantity')
                            ->label('Reserved Quantity')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('units')
                            ->helperText('Locked by pending/confirmed orders.'),

                        Placeholder::make('available')
                            ->label('Available (calculated)')
                            ->content(fn ($record) => $record
                                ? ($record->quantity - $record->reserved_quantity) . ' units'
                                : '—'
                            )
                            ->columnSpanFull(),

                        DateTimePicker::make('last_restock_date')
                            ->label('Last Restock Date')
                            ->native(false)
                            ->nullable()
                            ->placeholder('Not restocked yet')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
