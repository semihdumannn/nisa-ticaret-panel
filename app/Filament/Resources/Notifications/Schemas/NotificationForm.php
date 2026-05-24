<?php

namespace App\Filament\Resources\Notifications\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Notification Details')
                ->schema([
                    Placeholder::make('user')
                        ->label('Recipient')
                        ->content(fn ($record) => $record?->user?->name ?? '—'),

                    Placeholder::make('type')
                        ->label('Type')
                        ->content(fn ($record) => $record?->notificationType()?->label() ?? '—'),

                    Placeholder::make('title')
                        ->label('Title')
                        ->content(fn ($record) => $record?->title ?? '—'),

                    Placeholder::make('body')
                        ->label('Body')
                        ->content(fn ($record) => $record?->body ?? '—'),

                    Placeholder::make('is_read')
                        ->label('Read?')
                        ->content(fn ($record) => $record?->is_read ? 'Yes' : 'No'),

                    Placeholder::make('created_at')
                        ->label('Sent At')
                        ->content(fn ($record) => $record?->created_at?->toDateTimeString() ?? '—'),
                ]),
        ]);
    }
}
