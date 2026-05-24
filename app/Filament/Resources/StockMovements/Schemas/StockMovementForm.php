<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use App\Modules\Inventory\Domain\ValueObjects\MovementType;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Movement Details')->columns(2)->schema([
                Placeholder::make('product_name')
                    ->label('Product')
                    ->content(fn ($record) => $record?->product?->name ?? '—'),

                Placeholder::make('warehouse_name')
                    ->label('Warehouse')
                    ->content(fn ($record) => $record?->warehouse?->name ?? '—'),

                Placeholder::make('type_label')
                    ->label('Type')
                    ->content(fn ($record) => $record
                        ? MovementType::from($record->type)->label()
                        : '—'
                    ),

                Placeholder::make('quantity')
                    ->label('Quantity')
                    ->content(fn ($record) => $record?->quantity ?? '—'),

                Placeholder::make('variant_name')
                    ->label('Variant')
                    ->content(fn ($record) => $record?->variant?->name ?? '—'),

                Placeholder::make('user_name')
                    ->label('Performed By')
                    ->content(fn ($record) => $record?->user?->name ?? 'System'),
            ]),

            Section::make('Audit')->schema([
                Textarea::make('reason')
                    ->label('Reason / Notes')
                    ->disabled()
                    ->rows(2)
                    ->columnSpanFull(),

                Placeholder::make('created_at')
                    ->label('Recorded At')
                    ->content(fn ($record) => $record?->created_at?->format('d M Y H:i') ?? '—'),
            ]),
        ]);
    }
}
