<?php

namespace App\Filament\Resources\Campaigns\Tables;

use App\Modules\Campaign\Domain\ValueObjects\CampaignType;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Campaign')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => CampaignType::from($state)->label())
                    ->color(fn (string $state) => CampaignType::from($state)->color()),

                TextColumn::make('value')
                    ->label('Value')
                    ->formatStateUsing(fn ($state, $record) => $record->type === CampaignType::PERCENTAGE->value
                        ? number_format((float) $state, 2) . '%'
                        : '₺' . number_format((float) $state, 2)),

                TextColumn::make('start_date')
                    ->label('Starts')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Ends')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

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
                    ->options(collect(CampaignType::cases())->mapWithKeys(
                        fn (CampaignType $t) => [$t->value => $t->label()]
                    )),

                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
