<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Exports\OrdersExport;
use App\Filament\Resources\Orders\OrderResource;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->modalHeading('Export Orders')
                ->modalDescription('Filter which orders to include in the export.')
                ->modalWidth('md')
                ->form([
                    Select::make('status')
                        ->label('Status')
                        ->options(OrderStatus::options())
                        ->placeholder('All statuses')
                        ->nullable(),

                    DatePicker::make('from_date')
                        ->label('From Date')
                        ->native(false)
                        ->nullable(),

                    DatePicker::make('to_date')
                        ->label('To Date')
                        ->native(false)
                        ->nullable(),
                ])
                ->action(fn (array $data) => Excel::download(
                    new OrdersExport(
                        status:   $data['status'] ?? null,
                        fromDate: $data['from_date'] ?? null,
                        toDate:   $data['to_date'] ?? null,
                    ),
                    'orders-' . now()->format('Y-m-d') . '.xlsx'
                )),
        ];
    }
}
