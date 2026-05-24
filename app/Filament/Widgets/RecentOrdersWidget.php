<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Recent Orders';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $maxItems = 8;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::with(['customer', 'items'])
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(false),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => OrderStatus::from($state)->label())
                    ->color(fn (string $state) => OrderStatus::from($state)->color()),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('TRY'),

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at->format('d M Y H:i')),
            ])
            ->recordUrl(fn (Order $record) => OrderResource::getUrl('view', ['record' => $record]))
            ->paginated(false);
    }
}
