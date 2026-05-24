<?php

namespace App\Filament\Resources\Inventories\Tables;

use App\Models\Warehouse;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class InventoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->product?->sku),

                TextColumn::make('warehouse.name')
                    ->label('Warehouse')
                    ->badge()
                    ->color('info'),

                TextColumn::make('variant.name')
                    ->label('Variant')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('quantity')
                    ->label('On Hand')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('reserved_quantity')
                    ->label('Reserved')
                    ->numeric()
                    ->sortable()
                    ->color('warning'),

                TextColumn::make('available')
                    ->label('Available')
                    ->getStateUsing(fn ($record) => $record->availableQuantity())
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->availableQuantity() === 0 => 'danger',
                        $record->isLowStock()              => 'warning',
                        default                            => 'success',
                    }),

                TextColumn::make('last_restock_date')
                    ->label('Last Restock')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->placeholder('Never'),
            ])
            ->filters([
                SelectFilter::make('warehouse_id')
                    ->label('Warehouse')
                    ->options(Warehouse::active()->orderBy('name')->pluck('name', 'id')),

                TernaryFilter::make('low_stock')
                    ->label('Stock Level')
                    ->trueLabel('Low stock only')
                    ->falseLabel('In stock only')
                    ->queries(
                        true:  fn ($q) => $q->whereRaw('(quantity - reserved_quantity) <= 5'),
                        false: fn ($q) => $q->whereRaw('(quantity - reserved_quantity) > 5'),
                        blank: fn ($q) => $q,
                    ),
            ])
            ->recordActions([EditAction::make()])
            ->defaultSort('product.name');
    }
}
