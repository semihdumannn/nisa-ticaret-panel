<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Modules\Product\Domain\ValueObjects\ProductUnit;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // ── Hero Image ────────────────────────────────────────────────────
            Section::make()
                ->schema([
                    ImageEntry::make('images.image_url')
                        ->label('')
                        ->height(200)
                        ->extraImgAttributes(['class' => 'object-contain'])
                        ->defaultImageUrl(asset('images/placeholder.png'))
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed(false),

            // ── Product Details ───────────────────────────────────────────────
            Section::make('Product Details')
                ->icon('heroicon-o-cube')
                ->columns(3)
                ->schema([
                    TextEntry::make('name')
                        ->label('Product Name')
                        ->weight('bold')
                        ->size('lg')
                        ->columnSpan(2),

                    TextEntry::make('sku')
                        ->label('SKU')
                        ->copyable()
                        ->badge()
                        ->color('gray'),

                    TextEntry::make('brand.name')
                        ->label('Brand')
                        ->badge()
                        ->color('primary')
                        ->placeholder('—'),

                    TextEntry::make('unit')
                        ->label('Unit')
                        ->formatStateUsing(fn (string $state) => ProductUnit::tryFrom($state)?->label() ?? $state),

                    TextEntry::make('barcode')
                        ->label('Barcode')
                        ->copyable()
                        ->placeholder('—'),

                    IconEntry::make('is_active')
                        ->label('Active')
                        ->boolean(),

                    IconEntry::make('is_featured')
                        ->label('Featured')
                        ->boolean(),

                    TextEntry::make('min_order_qty')
                        ->label('Min Order Qty'),

                    TextEntry::make('max_order_qty')
                        ->label('Max Order Qty')
                        ->placeholder('—'),
                ]),

            // ── Pricing ───────────────────────────────────────────────────────
            Section::make('Pricing')
                ->icon('heroicon-o-banknotes')
                ->columns(3)
                ->schema([
                    TextEntry::make('price')
                        ->label('Selling Price')
                        ->money('TRY')
                        ->weight('bold')
                        ->size('lg'),

                    TextEntry::make('cost_price')
                        ->label('Cost Price')
                        ->money('TRY')
                        ->placeholder('—'),

                    TextEntry::make('tax_rate')
                        ->label('Tax Rate')
                        ->suffix('%')
                        ->numeric(decimalPlaces: 0),

                    TextEntry::make('price')
                        ->label('Price with Tax')
                        ->money('TRY')
                        ->getStateUsing(fn ($record) => $record->priceWithTax()),

                    TextEntry::make('margin')
                        ->label('Margin')
                        ->suffix('%')
                        ->getStateUsing(fn ($record) => $record->marginPercent() !== null
                            ? number_format($record->marginPercent(), 1)
                            : '—'),
                ]),

            // ── Categories ────────────────────────────────────────────────────
            Section::make('Categories')
                ->icon('heroicon-o-tag')
                ->schema([
                    TextEntry::make('categories.name')
                        ->label('')
                        ->badge()
                        ->color('info')
                        ->separator(',')
                        ->placeholder('No categories'),
                ]),

            // ── Variants ─────────────────────────────────────────────────────
            Section::make('Variants')
                ->icon('heroicon-o-squares-2x2')
                ->schema([
                    RepeatableEntry::make('variants')
                        ->label('')
                        ->columns(4)
                        ->schema([
                            TextEntry::make('name')
                                ->label('Variant Name')
                                ->weight('medium'),

                            TextEntry::make('sku')
                                ->label('Variant SKU')
                                ->copyable()
                                ->placeholder('—'),

                            TextEntry::make('price')
                                ->label('Price Override')
                                ->money('TRY')
                                ->placeholder('—'),

                            TextEntry::make('barcode')
                                ->label('Barcode')
                                ->placeholder('—'),
                        ])
                        ->placeholder('No variants'),
                ]),

            // ── Description ───────────────────────────────────────────────────
            Section::make('Description')
                ->icon('heroicon-o-document-text')
                ->collapsed()
                ->schema([
                    TextEntry::make('description')
                        ->label('')
                        ->html()
                        ->columnSpanFull()
                        ->placeholder('No description'),
                ]),

            // ── Meta ──────────────────────────────────────────────────────────
            Section::make('Timestamps')
                ->icon('heroicon-o-clock')
                ->collapsed()
                ->columns(2)
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Created')
                        ->dateTime('d M Y H:i'),

                    TextEntry::make('updated_at')
                        ->label('Last Updated')
                        ->dateTime('d M Y H:i'),
                ]),
        ]);
    }
}
