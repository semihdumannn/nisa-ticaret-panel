<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Brand;
use App\Models\Category;
use App\Modules\Product\Domain\ValueObjects\ProductUnit;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // ── Core Info ─────────────────────────────────────────────────────
            Section::make('Product Information')->columns(2)->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(200)
                    ->columnSpanFull(),

                TextInput::make('sku')
                    ->label('SKU')
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Auto-generated'),

                TextInput::make('barcode')
                    ->maxLength(50),

                TextInput::make('slug')
                    ->maxLength(200)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Auto-generated'),

                Select::make('unit')
                    ->options(ProductUnit::options())
                    ->default('piece')
                    ->required(),

                Textarea::make('description')
                    ->columnSpanFull()
                    ->rows(4),
            ]),

            // ── Brand & Categories ────────────────────────────────────────────
            Section::make('Classification')->columns(2)->schema([
                Select::make('brand_id')
                    ->label('Brand')
                    ->options(Brand::active()->ordered()->pluck('name', 'id'))
                    ->nullable()
                    ->searchable(),

                Select::make('categories')
                    ->label('Categories')
                    ->relationship('categories', 'name')
                    ->options(Category::active()->ordered()->pluck('name', 'id'))
                    ->multiple()
                    ->searchable(),
            ]),

            // ── Pricing ───────────────────────────────────────────────────────
            Section::make('Pricing')->columns(3)->schema([
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('₺')
                    ->minValue(0),

                TextInput::make('cost_price')
                    ->label('Cost Price')
                    ->numeric()
                    ->prefix('₺')
                    ->nullable(),

                TextInput::make('tax_rate')
                    ->label('Tax Rate (%)')
                    ->numeric()
                    ->default(20)
                    ->suffix('%'),

                TextInput::make('min_order_qty')
                    ->label('Min Order Qty')
                    ->numeric()
                    ->default(1)
                    ->minValue(1),

                TextInput::make('max_order_qty')
                    ->label('Max Order Qty')
                    ->numeric()
                    ->nullable(),
            ]),

            // ── Images ────────────────────────────────────────────────────────
            Section::make('Product Images')->schema([
                FileUpload::make('product_images_upload')
                    ->label('Images')
                    ->image()
                    ->multiple()
                    ->directory('products')
                    ->disk('public')
                    ->reorderable()
                    ->hint('First image will be set as primary.'),
            ]),

            // ── Variants ──────────────────────────────────────────────────────
            Section::make('Variants (optional)')->schema([
                Repeater::make('variants')
                    ->relationship('variants')
                    ->columns(3)
                    ->schema([
                        TextInput::make('sku')
                            ->label('Variant SKU')
                            ->required()
                            ->maxLength(50),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('e.g. 500ml Plastic'),

                        TextInput::make('price_adjustment')
                            ->label('Price +/-')
                            ->numeric()
                            ->default(0)
                            ->prefix('₺'),

                        TextInput::make('stock')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->default(true),
                    ])
                    ->collapsed()
                    ->itemLabel(fn (array $state) => $state['name'] ?? 'New Variant'),
            ]),

            // ── Settings ──────────────────────────────────────────────────────
            Section::make('Visibility')->columns(2)->schema([
                Toggle::make('is_featured')
                    ->label('Featured Product')
                    ->onColor('warning'),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->onColor('success'),
            ]),
        ]);
    }
}
