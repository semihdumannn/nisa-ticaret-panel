<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Brand;
use App\Models\Category;
use App\Modules\Product\Domain\ValueObjects\ProductUnit;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([

                // ── Left column (2/3 width) ───────────────────────────────────
                Section::make('Product Information')
                    ->description('Name, description, and identifiers.')
                    ->icon('heroicon-o-cube')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Product Name')
                            ->required()
                            ->maxLength(200)
                            ->columnSpanFull(),

                        TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Auto-generated if left blank'),

                        TextInput::make('barcode')
                            ->label('Barcode')
                            ->maxLength(50)
                            ->placeholder('EAN / QR code'),

                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->maxLength(200)
                            ->unique(ignoreRecord: true)
                            ->placeholder('auto-generated-from-name'),

                        Select::make('unit')
                            ->label('Unit of Measure')
                            ->options(ProductUnit::options())
                            ->default('piece')
                            ->native(false)
                            ->required(),

                        Textarea::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->rows(4)
                            ->placeholder('Detailed product description…'),
                    ]),

                // ── Right column (1/3 width) ──────────────────────────────────
                Section::make('Visibility')
                    ->description('Control how the product appears.')
                    ->icon('heroicon-o-eye')
                    ->columnSpan(1)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active (visible in app)')
                            ->default(true)
                            ->onColor('success')
                            ->helperText('Inactive products are hidden from customers.'),

                        Toggle::make('is_featured')
                            ->label('Featured Product')
                            ->onColor('warning')
                            ->helperText('Shown in featured sections on home screen.'),

                        TextInput::make('sort_order')
                            ->label('Sıralama')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->helperText('Düşük sayı → önce gösterilir.'),
                    ]),

                // ── Classification ────────────────────────────────────────────
                Section::make('Classification')
                    ->description('Assign brand and categories.')
                    ->icon('heroicon-o-tag')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('brand_id')
                            ->label('Brand')
                            ->options(Brand::active()->ordered()->pluck('name', 'id'))
                            ->nullable()
                            ->native(false)
                            ->searchable()
                            ->placeholder('No brand'),

                        Select::make('categories')
                            ->label('Categories')
                            ->relationship('categories', 'name')
                            ->options(Category::active()->ordered()->pluck('name', 'id'))
                            ->multiple()
                            ->native(false)
                            ->searchable()
                            ->placeholder('Select categories…'),
                    ]),

                // ── Pricing ───────────────────────────────────────────────────
                Section::make('Pricing')
                    ->description('Set prices, tax rate, and order limits.')
                    ->icon('heroicon-o-banknotes')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextInput::make('price')
                            ->label('Selling Price')
                            ->required()
                            ->numeric()
                            ->prefix('₺')
                            ->minValue(0)
                            ->step(0.01)
                            ->placeholder('0.00'),

                        TextInput::make('cost_price')
                            ->label('Cost Price')
                            ->numeric()
                            ->prefix('₺')
                            ->step(0.01)
                            ->nullable()
                            ->placeholder('0.00')
                            ->helperText('Not visible to customers.'),

                        TextInput::make('tax_rate')
                            ->label('Tax Rate')
                            ->numeric()
                            ->default(20)
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(1),

                        TextInput::make('min_order_qty')
                            ->label('Min Order Qty')
                            ->numeric()
                            ->integer()
                            ->default(1)
                            ->minValue(1),

                        TextInput::make('max_order_qty')
                            ->label('Max Order Qty')
                            ->numeric()
                            ->integer()
                            ->nullable()
                            ->placeholder('Unlimited'),
                    ]),

                // ── Product Images ────────────────────────────────────────────
                Section::make('Product Images')
                    ->description('Upload up to 10 images. First image becomes primary.')
                    ->icon('heroicon-o-photo')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('product_images_upload')
                            ->label('Images')
                            ->image()
                            ->multiple()
                            ->maxFiles(10)
                            ->directory('products')
                            ->disk('public')
                            ->reorderable()
                            ->hint('Drag to reorder — first image is the primary thumbnail.'),
                    ]),

                // ── Variants ──────────────────────────────────────────────────
                Section::make('Product Variants')
                    ->description('Optional — add sizes, colors, volumes, etc.')
                    ->icon('heroicon-o-squares-2x2')
                    ->collapsed()
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('variants')
                            ->relationship('variants')
                            ->label('')
                            ->columns(5)
                            ->schema([
                                TextInput::make('sku')
                                    ->label('Variant SKU')
                                    ->required()
                                    ->maxLength(50),

                                TextInput::make('name')
                                    ->label('Variant Name')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('e.g. 500ml Bottle'),

                                TextInput::make('price_adjustment')
                                    ->label('Price Δ')
                                    ->numeric()
                                    ->step(0.01)
                                    ->default(0)
                                    ->prefix('₺')
                                    ->helperText('Added to base price.'),

                                TextInput::make('stock')
                                    ->label('Stock')
                                    ->numeric()
                                    ->integer()
                                    ->default(0)
                                    ->minValue(0),

                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->inline(false),
                            ])
                            ->collapsed()
                            ->itemLabel(fn (array $state) => $state['name'] ?? 'New Variant')
                            ->addActionLabel('Add Variant'),
                    ]),
            ]);
    }
}
