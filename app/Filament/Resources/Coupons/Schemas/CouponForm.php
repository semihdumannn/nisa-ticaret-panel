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
        return $schema->components([

            Section::make('Coupon Details')
                ->columns(2)
                ->schema([
                    TextInput::make('code')
                        ->required()
                        ->maxLength(50)
                        ->alphaDash()
                        ->dehydrateStateUsing(fn (string $state) => strtoupper($state))
                        ->placeholder('e.g. SUMMER20'),

                    Select::make('type')
                        ->label('Discount Type')
                        ->required()
                        ->options(collect(CouponType::cases())->mapWithKeys(
                            fn (CouponType $t) => [$t->value => $t->label()]
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

                    TextInput::make('usage_limit')
                        ->label('Usage Limit')
                        ->numeric()
                        ->integer()
                        ->minValue(1)
                        ->placeholder('Unlimited'),
                ]),

            Section::make('Schedule & Settings')
                ->columns(2)
                ->schema([
                    DateTimePicker::make('start_date')
                        ->required()
                        ->native(false),

                    DateTimePicker::make('end_date')
                        ->required()
                        ->native(false),

                    Toggle::make('user_specific')
                        ->label('Single-use per user')
                        ->helperText('Prevent the same user from using this coupon twice.')
                        ->default(false),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ]),

        ]);
    }
}
