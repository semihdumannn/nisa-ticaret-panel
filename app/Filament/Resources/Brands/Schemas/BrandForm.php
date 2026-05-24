<?php

namespace App\Filament\Resources\Brands\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Brand Details')->columns(2)->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(100),

                TextInput::make('slug')
                    ->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Auto-generated from name'),

                Textarea::make('description')
                    ->columnSpanFull()
                    ->rows(3),
            ]),

            Section::make('Logo & Settings')->columns(2)->schema([
                FileUpload::make('logo_url')
                    ->label('Logo')
                    ->image()
                    ->directory('brands')
                    ->disk('public')
                    ->columnSpanFull(),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_active')
                    ->default(true)
                    ->onColor('success'),
            ]),
        ]);
    }
}
