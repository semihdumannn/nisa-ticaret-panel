<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Order Items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->weight('medium'),

                TextColumn::make('variant.name')
                    ->label('Variant')
                    ->placeholder('—'),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('unit_price')
                    ->label('Unit Price')
                    ->money('TRY'),

                TextColumn::make('discount_amount')
                    ->label('Discount')
                    ->money('TRY')
                    ->toggleable(),

                TextColumn::make('total_price')
                    ->label('Total')
                    ->money('TRY')
                    ->weight('bold'),
            ])
            ->paginated(false)
            ->heading('Order Items');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
