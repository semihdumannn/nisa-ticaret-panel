<?php

namespace App\Filament\Resources\StockMovements\Tables;

use App\Models\Warehouse;
use App\Modules\Inventory\Domain\ValueObjects\MovementType;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

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

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => MovementType::from($state)->label())
                    ->color(fn (string $state) => MovementType::from($state)->color()),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($record) => $record->quantity >= 0 ? 'success' : 'danger'),

                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->reason)
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('By')
                    ->placeholder('System')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Movement Type')
                    ->options(MovementType::options()),

                SelectFilter::make('warehouse_id')
                    ->label('Warehouse')
                    ->options(Warehouse::active()->orderBy('name')->pluck('name', 'id')),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
