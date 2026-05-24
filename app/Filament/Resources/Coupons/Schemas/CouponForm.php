<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Modules\Campaign\Domain\ValueObjects\CouponType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([

                // ── Coupon Code & Discount (2/3) ──────────────────────────────
                Section::make('Coupon Details')
                    ->description('The code customers enter at checkout.')
                    ->icon('heroicon-o-ticket')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Coupon Code')
                            ->required()
                            ->maxLength(50)
                            ->alphaDash()
                            ->dehydrateStateUsing(fn (string $state) => strtoupper($state))
                            ->placeholder('e.g. SUMMER20')
                            ->helperText('Letters, numbers, and dashes only. Automatically uppercased.')
                            ->columnSpanFull(),

                        Select::make('type')
                            ->label('Discount Type')
                            ->required()
                            ->native(false)
                            ->options(collect(CouponType::cases())->mapWithKeys(
                                fn (CouponType $t) => [$t->value => $t->label()]
                            ))
                            ->live(),

                        TextInput::make('value')
                            ->label('Discount Value')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix(fn ($get) => $get('type') === 'fixed' ? '₺' : null)
                            ->suffix(fn ($get) => $get('type') === 'percentage' ? '%' : null)
                            ->placeholder('0.00'),

                        TextInput::make('min_purchase_amount')
                            ->label('Min Purchase Amount')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('₺')
                            ->nullable()
                            ->placeholder('No minimum'),

                        TextInput::make('max_discount_amount')
                            ->label('Max Discount Cap')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('₺')
                            ->nullable()
                            ->placeholder('No cap'),
                    ]),

                // ── Settings (1/3) ────────────────────────────────────────────
                Section::make('Settings')
                    ->description('Usage rules and activation.')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columnSpan(1)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->onColor('success'),

                        Toggle::make('user_specific')
                            ->label('Single-use per user')
                            ->default(false)
                            ->helperText('Prevents the same user from using this coupon more than once.'),

                        TextInput::make('usage_limit')
                            ->label('Total Usage Limit')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->nullable()
                            ->placeholder('Unlimited'),
                    ]),

                // ── Schedule ──────────────────────────────────────────────────
                Section::make('Validity Period')
                    ->description('Coupon will only be accepted within this date range.')
                    ->icon('heroicon-o-calendar-days')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('start_date')
                            ->label('Valid From')
                            ->required()
                            ->native(false)
                            ->seconds(false),

                        DateTimePicker::make('end_date')
                            ->label('Valid Until')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->after('start_date'),
                    ]),
            ]);
    }
}
