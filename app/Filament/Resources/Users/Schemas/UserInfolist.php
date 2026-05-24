<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Modules\User\Domain\ValueObjects\UserRole;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // ── Identity ──────────────────────────────────────────────────────
            Section::make('Identity')
                ->icon('heroicon-o-user')
                ->columns(3)
                ->schema([
                    TextEntry::make('name')
                        ->label('Full Name')
                        ->weight('bold')
                        ->size('lg')
                        ->placeholder('—'),

                    TextEntry::make('phone')
                        ->label('Phone')
                        ->copyable(),

                    TextEntry::make('email')
                        ->label('Email')
                        ->copyable()
                        ->placeholder('—'),

                    TextEntry::make('role')
                        ->label('Role')
                        ->badge()
                        ->color(fn (string $state) => match ($state) {
                            'admin'       => 'danger',
                            'field_agent' => 'warning',
                            'delivery'    => 'info',
                            default       => 'success',
                        })
                        ->formatStateUsing(fn (string $state) => UserRole::from($state)->label()),

                    IconEntry::make('is_active')
                        ->label('Active')
                        ->boolean(),

                    TextEntry::make('created_at')
                        ->label('Registered At')
                        ->dateTime('d M Y H:i'),
                ]),

            // ── Addresses ─────────────────────────────────────────────────────
            Section::make('Addresses')
                ->icon('heroicon-o-map-pin')
                ->schema([
                    RepeatableEntry::make('addresses')
                        ->label('')
                        ->columns(3)
                        ->schema([
                            TextEntry::make('title')
                                ->label('Title')
                                ->weight('medium'),

                            IconEntry::make('is_default')
                                ->label('Default')
                                ->boolean(),

                            TextEntry::make('city')
                                ->label('City'),

                            TextEntry::make('full_address')
                                ->label('Address')
                                ->columnSpanFull()
                                ->placeholder('—'),
                        ])
                        ->placeholder('No addresses'),
                ]),

            // ── Recent Orders ─────────────────────────────────────────────────
            Section::make('Recent Orders')
                ->icon('heroicon-o-shopping-cart')
                ->schema([
                    RepeatableEntry::make('orders')
                        ->label('')
                        ->columns(4)
                        ->schema([
                            TextEntry::make('order_number')
                                ->label('Order #')
                                ->weight('medium')
                                ->copyable(),

                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->formatStateUsing(fn (string $state) => \App\Modules\Order\Domain\ValueObjects\OrderStatus::from($state)->label())
                                ->color(fn (string $state) => \App\Modules\Order\Domain\ValueObjects\OrderStatus::from($state)->color()),

                            TextEntry::make('total')
                                ->label('Total')
                                ->money('TRY')
                                ->weight('bold'),

                            TextEntry::make('created_at')
                                ->label('Date')
                                ->date('d M Y'),
                        ])
                        ->placeholder('No orders yet'),
                ]),

            // ── Auth Info ─────────────────────────────────────────────────────
            Section::make('Auth Details')
                ->icon('heroicon-o-key')
                ->collapsed()
                ->columns(2)
                ->schema([
                    TextEntry::make('firebase_uid')
                        ->label('Firebase UID')
                        ->copyable()
                        ->placeholder('—'),

                    TextEntry::make('email_verified_at')
                        ->label('Email Verified At')
                        ->dateTime('d M Y H:i')
                        ->placeholder('Not verified'),

                    TextEntry::make('updated_at')
                        ->label('Last Updated')
                        ->dateTime('d M Y H:i'),

                    TextEntry::make('deleted_at')
                        ->label('Deleted At')
                        ->dateTime('d M Y H:i')
                        ->placeholder('—'),
                ]),
        ]);
    }
}
