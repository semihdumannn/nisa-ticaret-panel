<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use App\Modules\Order\Domain\ValueObjects\PaymentStatus;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->weight('bold')
                    ->copyable()
                    ->url(fn (Order $record) => OrderResource::getUrl('view', ['record' => $record])),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.phone')
                    ->label('Phone')
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => OrderStatus::from($state)->label())
                    ->color(fn (string $state) => OrderStatus::from($state)->color()),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => PaymentStatus::from($state)->label())
                    ->color(fn (string $state) => PaymentStatus::from($state)->color())
                    ->toggleable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('TRY')
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('scheduled_delivery_date')
                    ->label('Delivery Date')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Placed At')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at->format('d M Y H:i')),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(OrderStatus::options()),

                SelectFilter::make('payment_status')
                    ->label('Payment')
                    ->options(PaymentStatus::options()),
            ])
            ->recordUrl(fn (Order $record) => OrderResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (Order $record) => OrderResource::getUrl('view', ['record' => $record])),

                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
