<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Modules\Campaign\Domain\ValueObjects\CouponType;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->weight('bold')
                    ->copyable()
                    ->fontFamily('mono'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => CouponType::from($state)->label())
                    ->color(fn (string $state) => CouponType::from($state)->color()),

                TextColumn::make('value')
                    ->label('Discount')
                    ->formatStateUsing(fn ($state, $record) => $record->type === CouponType::PERCENTAGE->value
                        ? number_format((float) $state, 2) . '%'
                        : '₺' . number_format((float) $state, 2)),

                TextColumn::make('end_date')
                    ->label('Expires')
                    ->dateTime('d M Y')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                IconColumn::make('user_specific')
                    ->label('Single-use')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('usage_count')
                    ->label('Used')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state, $record) => $record->usage_limit
                        ? "{$state}/{$record->usage_limit}"
                        : $state),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(collect(CouponType::cases())->mapWithKeys(
                        fn (CouponType $t) => [$t->value => $t->label()]
                    )),

                TernaryFilter::make('is_active')
                    ->label('Active'),

                TernaryFilter::make('user_specific')
                    ->label('Single-use per user'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
