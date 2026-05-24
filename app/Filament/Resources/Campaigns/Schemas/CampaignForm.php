<?php

namespace App\Filament\Resources\Campaigns\Schemas;

use App\Modules\Campaign\Domain\ValueObjects\CampaignType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Campaign Details')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(200)
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),

                    Select::make('type')
                        ->label('Campaign Type')
                        ->required()
                        ->options(collect(CampaignType::cases())->mapWithKeys(
                            fn (CampaignType $t) => [$t->value => $t->label()]
                        ))
                        ->native(false),

                    TextInput::make('value')
                        ->label('Discount Value')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01),

                    TextInput::make('min_purchase_amount')
                        ->label('Min Purchase Amount')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->placeholder('No minimum'),

                    TextInput::make('max_discount_amount')
                        ->label('Max Discount Amount')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->placeholder('No cap'),
                ]),

            Section::make('Schedule & Limits')
                ->columns(2)
                ->schema([
                    DateTimePicker::make('start_date')
                        ->required()
                        ->native(false),

                    DateTimePicker::make('end_date')
                        ->required()
                        ->native(false),

                    TextInput::make('usage_limit')
                        ->label('Usage Limit')
                        ->numeric()
                        ->integer()
                        ->minValue(1)
                        ->placeholder('Unlimited'),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ]),

        ]);
    }
}
