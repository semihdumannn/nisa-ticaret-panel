<?php

namespace App\Filament\Resources\Campaigns\Schemas;

use App\Modules\Campaign\Domain\ValueObjects\CampaignType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([

                // ── Campaign Details (2/3 width) ──────────────────────────────
                Section::make('Campaign Details')
                    ->description('Define the campaign name, type, and discount value.')
                    ->icon('heroicon-o-megaphone')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Campaign Name')
                            ->required()
                            ->maxLength(200)
                            ->columnSpanFull()
                            ->placeholder('e.g. Summer Sale 2026'),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(2)
                            ->columnSpanFull()
                            ->placeholder('Brief description for internal reference…'),

                        Select::make('type')
                            ->label('Discount Type')
                            ->required()
                            ->native(false)
                            ->options(collect(CampaignType::cases())->mapWithKeys(
                                fn (CampaignType $t) => [$t->value => $t->label()]
                            ))
                            ->live()
                            ->helperText('Percentage: e.g. 10% off. Fixed: e.g. ₺15 off.'),

                        TextInput::make('value')
                            ->label('Discount Value')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix(fn ($get) => $get('type') === CampaignType::FIXED->value ? '₺' : null)
                            ->suffix(fn ($get) => $get('type') === CampaignType::PERCENTAGE->value ? '%' : null)
                            ->placeholder('0.00'),
                    ]),

                // ── Settings (1/3 width) ──────────────────────────────────────
                Section::make('Settings')
                    ->description('Status and usage constraints.')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columnSpan(1)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->onColor('success'),

                        TextInput::make('usage_limit')
                            ->label('Usage Limit')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->nullable()
                            ->placeholder('Unlimited'),
                    ]),

                // ── Spending Thresholds ───────────────────────────────────────
                Section::make('Spending Thresholds')
                    ->description('Optional minimum purchase and maximum discount cap.')
                    ->icon('heroicon-o-scale')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('min_purchase_amount')
                            ->label('Minimum Purchase Amount')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('₺')
                            ->nullable()
                            ->placeholder('No minimum'),

                        TextInput::make('max_discount_amount')
                            ->label('Maximum Discount Cap')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('₺')
                            ->nullable()
                            ->placeholder('No cap'),
                    ]),

                // ── Schedule ──────────────────────────────────────────────────
                Section::make('Schedule')
                    ->description('Campaign active period.')
                    ->icon('heroicon-o-calendar-days')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('start_date')
                            ->label('Start Date & Time')
                            ->required()
                            ->native(false)
                            ->seconds(false),

                        DateTimePicker::make('end_date')
                            ->label('End Date & Time')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->after('start_date'),
                    ]),
            ]);
    }
}
