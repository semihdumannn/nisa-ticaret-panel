<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                // ── Category Details (2/3) ────────────────────────────────────
                Section::make('Category Details')
                    ->description('Name, URL slug, and hierarchy.')
                    ->icon('heroicon-o-folder')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Category Name')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('e.g. Soft Drinks'),

                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->maxLength(100)
                            ->unique(ignoreRecord: true)
                            ->placeholder('auto-generated'),

                        Select::make('parent_id')
                            ->label('Parent Category')
                            ->options(Category::active()->root()->ordered()->pluck('name', 'id'))
                            ->nullable()
                            ->native(false)
                            ->searchable()
                            ->placeholder('(Root — top-level category)')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(2)
                            ->columnSpanFull()
                            ->placeholder('Brief category description for the app…'),
                    ]),

                // ── Appearance & Settings (1/3) ───────────────────────────────
                Section::make('Appearance & Settings')
                    ->description('Visual customisation and sort order.')
                    ->icon('heroicon-o-paint-brush')
                    ->columnSpan(1)
                    ->schema([
                        ColorPicker::make('color')
                            ->label('Brand Color')
                            ->helperText('Used for category badges and highlights.'),

                        TextInput::make('icon')
                            ->label('Icon')
                            ->maxLength(50)
                            ->placeholder('e.g. heroicon-o-tag')
                            ->helperText('Heroicon name for mobile app display.'),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->helperText('Lower numbers appear first.'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->onColor('success')
                            ->helperText('Inactive categories are hidden from customers.'),
                    ]),
            ]);
    }
}
