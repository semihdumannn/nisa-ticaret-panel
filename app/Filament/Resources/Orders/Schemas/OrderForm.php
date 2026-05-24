<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\User;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use App\Modules\Order\Domain\ValueObjects\PaymentStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Order Summary')->columns(3)->schema([
                Placeholder::make('order_number')
                    ->label('Order #')
                    ->content(fn ($record) => $record?->order_number ?? '—'),

                Placeholder::make('customer_name')
                    ->label('Customer')
                    ->content(fn ($record) => $record?->customer?->name ?? '—'),

                Placeholder::make('total_display')
                    ->label('Total')
                    ->content(fn ($record) => $record ? '₺ ' . number_format($record->total, 2) : '—'),
            ]),

            Section::make('Status Management')->columns(2)->schema([
                Select::make('status')
                    ->label('Order Status')
                    ->options(OrderStatus::options())
                    ->required(),

                Select::make('payment_status')
                    ->label('Payment Status')
                    ->options(PaymentStatus::options())
                    ->required(),

                Select::make('assigned_to')
                    ->label('Assigned Driver')
                    ->options(
                        User::whereHas('roles', fn ($q) => $q->where('name', 'delivery'))
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->nullable(),

                DatePicker::make('scheduled_delivery_date')
                    ->label('Scheduled Delivery'),
            ]),

            Section::make('Notes')->schema([
                Textarea::make('internal_notes')
                    ->label('Internal Notes (admin only)')
                    ->rows(3)
                    ->columnSpanFull(),
            ]),

        ]);
    }
}
