<?php

namespace App\Filament\Resources\Brands\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                // ── Brand Info (2/3) ──────────────────────────────────────────
                Section::make('Brand Details')
                    ->description('Name, slug, and description.')
                    ->icon('heroicon-o-tag')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Brand Name')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('e.g. Coca-Cola'),

                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->maxLength(100)
                            ->unique(ignoreRecord: true)
                            ->placeholder('auto-generated-from-name'),

                        Textarea::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->rows(3)
                            ->placeholder('Brief brand description for the app…'),
                    ]),

                // ── Logo & Settings (1/3) ─────────────────────────────────────
                Section::make('Logo & Ordering')
                    ->description('Visual identity and display order.')
                    ->icon('heroicon-o-photo')
                    ->columnSpan(1)
                    ->schema([
                        FileUpload::make('logo_url')
                            ->label('Logo')
                            ->image()
                            ->directory('brands')
                            ->disk('public')
                            ->hint('PNG or SVG recommended.'),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->helperText('Lower numbers appear first.'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->onColor('success'),
                    ]),
            ]);
    }
}
