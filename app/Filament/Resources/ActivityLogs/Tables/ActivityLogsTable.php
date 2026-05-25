<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('log_name')
                    ->label('Log')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'default' => 'gray',
                        'order'   => 'primary',
                        'product' => 'success',
                        'user'    => 'info',
                        default   => 'gray',
                    })
                    ->toggleable(),

                TextColumn::make('description')
                    ->label('Event')
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('subject_type')
                    ->label('Model')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('subject_id')
                    ->label('Record ID')
                    ->numeric()
                    ->toggleable(),

                TextColumn::make('causer.name')
                    ->label('Performed By')
                    ->placeholder('System / API'),

                TextColumn::make('properties')
                    ->label('Changed Fields')
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) {
                            return '—';
                        }
                        $data = is_array($state) ? $state : json_decode($state, true);
                        $attributes = array_keys($data['attributes'] ?? $data ?? []);
                        return implode(', ', array_slice($attributes, 0, 4))
                            . (count($attributes) > 4 ? ' +' . (count($attributes) - 4) . ' more' : '');
                    })
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('When')
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at->format('d M Y H:i:s'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('subject_type')
                    ->label('Model')
                    ->options([
                        'App\Models\User'    => 'User',
                        'App\Models\Product' => 'Product',
                        'App\Models\Order'   => 'Order',
                    ]),

                SelectFilter::make('log_name')
                    ->label('Log Name')
                    ->options([
                        'default' => 'Default',
                        'order'   => 'Order',
                        'product' => 'Product',
                        'user'    => 'User',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([25, 50, 100]);
    }
}
