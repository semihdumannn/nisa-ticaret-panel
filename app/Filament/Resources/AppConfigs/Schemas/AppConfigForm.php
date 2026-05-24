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
        return $schema
            ->columns(3)
            ->components([
                // ── Key & Value (2/3) ─────────────────────────────────────────
                Section::make('Configuration Entry')
                    ->description('Key-value pair stored in the database and cached.')
                    ->icon('heroicon-o-cog')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('key')
                            ->label('Config Key')
                            ->required()
                            ->maxLength(100)
                            ->alphaDash()
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull()
                            ->placeholder('e.g. min_order_amount')
                            ->helperText('Snake_case or dot.notation. Cannot be changed after creation.'),

                        Select::make('type')
                            ->label('Value Type')
                            ->required()
                            ->native(false)
                            ->options([
                                'string'  => 'String (text)',
                                'number'  => 'Number (integer / decimal)',
                                'boolean' => 'Boolean (true / false)',
                                'json'    => 'JSON (array or object)',
                            ]),

                        TextInput::make('value')
                            ->label('Value')
                            ->required()
                            ->maxLength(65535)
                            ->placeholder('Enter the value…'),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(2)
                            ->columnSpanFull()
                            ->placeholder('What does this config key control?'),
                    ]),

                // ── Info (1/3) ────────────────────────────────────────────────
                Section::make('Cache Note')
                    ->description('This value is cached for 1 hour. Changes take effect immediately — cache is invalidated on save.')
                    ->icon('heroicon-o-information-circle')
                    ->columnSpan(1)
                    ->schema([]),
            ]);
    }
}
