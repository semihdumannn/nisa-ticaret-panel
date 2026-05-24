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
        return $schema->components([
            Section::make('Category Details')->columns(2)->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(100),

                TextInput::make('slug')
                    ->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Auto-generated'),

                Select::make('parent_id')
                    ->label('Parent Category')
                    ->options(Category::active()->root()->ordered()->pluck('name', 'id'))
                    ->nullable()
                    ->searchable()
                    ->placeholder('(Root category)'),

                TextInput::make('icon')
                    ->maxLength(50)
                    ->placeholder('e.g. heroicon-o-tag'),

                ColorPicker::make('color')
                    ->label('Brand Color'),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),

                Textarea::make('description')
                    ->columnSpanFull()
                    ->rows(2),

                Toggle::make('is_active')
                    ->default(true)
                    ->onColor('success'),
            ]),
        ]);
    }
}
