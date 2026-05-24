<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventoryRelationManager extends RelationManager
{
    protected static string $relationship = 'inventories';

    protected static ?string $title = 'Stock by Warehouse';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('warehouse.name')
                    ->label('Warehouse')
                    ->weight('medium'),

                TextColumn::make('warehouse.city')
                    ->label('City')
                    ->placeholder('—'),

                TextColumn::make('quantity')
                    ->label('On Hand')
                    ->badge()
                    ->color('info'),

                TextColumn::make('reserved_quantity')
                    ->label('Reserved')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('available')
                    ->label('Available')
                    ->badge()
                    ->color(fn ($record) => ($record->quantity - $record->reserved_quantity) <= 5 ? 'danger' : 'success')
                    ->getStateUsing(fn ($record) => $record->quantity - $record->reserved_quantity),

                TextColumn::make('last_restock_date')
                    ->label('Last Restock')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->paginated(false);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
