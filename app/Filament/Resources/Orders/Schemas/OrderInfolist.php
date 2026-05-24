<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use App\Modules\Order\Domain\ValueObjects\PaymentStatus;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // ── Order Summary ─────────────────────────────────────────────────
            Section::make('Order Summary')
                ->icon('heroicon-o-clipboard-document-list')
                ->columns(3)
                ->schema([
                    TextEntry::make('order_number')
                        ->label('Order Number')
                        ->copyable()
                        ->weight('bold')
                        ->size('lg'),

                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn (string $state) => OrderStatus::from($state)->label())
                        ->color(fn (string $state) => OrderStatus::from($state)->color()),

                    TextEntry::make('payment_status')
                        ->label('Payment')
                        ->badge()
                        ->formatStateUsing(fn (string $state) => PaymentStatus::from($state)->label())
                        ->color(fn (string $state) => PaymentStatus::from($state)->color()),

                    TextEntry::make('subtotal')
                        ->label('Subtotal')
                        ->money('TRY'),

                    TextEntry::make('discount_amount')
                        ->label('Discount')
                        ->money('TRY'),

                    TextEntry::make('total')
                        ->label('Total')
                        ->money('TRY')
                        ->weight('bold')
                        ->size('lg'),

                    TextEntry::make('payment_method')
                        ->label('Payment Method')
                        ->badge()
                        ->color('gray'),

                    TextEntry::make('created_at')
                        ->label('Order Date')
                        ->dateTime('d M Y H:i'),

                    TextEntry::make('scheduled_delivery_date')
                        ->label('Scheduled Delivery')
                        ->date('d M Y')
                        ->placeholder('—'),
                ]),

            // ── Customer Info ─────────────────────────────────────────────────
            Section::make('Customer')
                ->icon('heroicon-o-user')
                ->columns(2)
                ->schema([
                    TextEntry::make('customer.name')
                        ->label('Name'),

                    TextEntry::make('customer.phone')
                        ->label('Phone')
                        ->copyable(),

                    TextEntry::make('customer.email')
                        ->label('Email')
                        ->copyable()
                        ->placeholder('—'),
                ]),

            // ── Delivery Address ──────────────────────────────────────────────
            Section::make('Delivery Address')
                ->icon('heroicon-o-map-pin')
                ->columns(2)
                ->schema([
                    TextEntry::make('address.title')
                        ->label('Address Title'),

                    TextEntry::make('address.full_address')
                        ->label('Full Address')
                        ->columnSpanFull(),

                    TextEntry::make('address.district')
                        ->label('District'),

                    TextEntry::make('address.city')
                        ->label('City'),

                    TextEntry::make('address.postal_code')
                        ->label('Postal Code'),
                ]),

            // ── Order Items ───────────────────────────────────────────────────
            Section::make('Order Items')
                ->icon('heroicon-o-shopping-cart')
                ->schema([
                    RepeatableEntry::make('items')
                        ->label('')
                        ->columns(5)
                        ->schema([
                            TextEntry::make('product.name')
                                ->label('Product')
                                ->weight('medium'),

                            TextEntry::make('variant.name')
                                ->label('Variant')
                                ->placeholder('—'),

                            TextEntry::make('quantity')
                                ->label('Qty')
                                ->badge()
                                ->color('gray'),

                            TextEntry::make('unit_price')
                                ->label('Unit Price')
                                ->money('TRY'),

                            TextEntry::make('total_price')
                                ->label('Subtotal')
                                ->money('TRY')
                                ->weight('bold'),
                        ]),
                ]),

            // ── Status History / Timeline ─────────────────────────────────────
            Section::make('Order Timeline')
                ->icon('heroicon-o-clock')
                ->schema([
                    RepeatableEntry::make('statusHistory')
                        ->label('')
                        ->columns(3)
                        ->schema([
                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->formatStateUsing(fn (string $state) => OrderStatus::from($state)->label())
                                ->color(fn (string $state) => OrderStatus::from($state)->color()),

                            TextEntry::make('createdBy.name')
                                ->label('By')
                                ->placeholder('System'),

                            TextEntry::make('created_at')
                                ->label('Time')
                                ->dateTime('d M Y H:i'),

                            TextEntry::make('note')
                                ->label('Note')
                                ->placeholder('—')
                                ->columnSpanFull(),
                        ]),
                ]),

            // ── Notes ─────────────────────────────────────────────────────────
            Section::make('Notes')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->collapsed()
                ->schema([
                    TextEntry::make('notes')
                        ->label('Customer Notes')
                        ->placeholder('—')
                        ->columnSpanFull(),

                    TextEntry::make('internal_notes')
                        ->label('Internal Notes')
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
