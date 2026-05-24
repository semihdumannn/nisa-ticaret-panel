<?php

namespace App\Filament\Resources\AppConfigs\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AppConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Config Entry')
                ->columns(2)
                ->schema([
                    TextInput::make('key')
                        ->required()
                        ->maxLength(100)
                        ->alphaDash()
                        ->unique(ignoreRecord: true)
                        ->columnSpanFull(),

                    Select::make('type')
                        ->required()
                        ->options([
                            'string'  => 'String',
                            'number'  => 'Number',
                            'boolean' => 'Boolean',
                            'json'    => 'JSON',
                        ])
                        ->native(false),

                    TextInput::make('value')
                        ->required()
                        ->maxLength(65535),

                    Textarea::make('description')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
