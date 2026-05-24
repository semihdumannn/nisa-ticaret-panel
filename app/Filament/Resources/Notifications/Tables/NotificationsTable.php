<?php

namespace App\Filament\Resources\Notifications\Tables;

use App\Modules\Notification\Domain\ValueObjects\NotificationType;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class NotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => NotificationType::from($state)->label())
                    ->color(fn (string $state) => NotificationType::from($state)->color()),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('body')
                    ->label('Body')
                    ->limit(60)
                    ->toggleable(),

                IconColumn::make('is_read')
                    ->label('Read')
                    ->boolean(),

                TextColumn::make('read_at')
                    ->label('Read At')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Sent At')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(collect(NotificationType::cases())->mapWithKeys(
                        fn (NotificationType $t) => [$t->value => $t->label()]
                    )),

                TernaryFilter::make('is_read')
                    ->label('Read'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([]);
    }
}
