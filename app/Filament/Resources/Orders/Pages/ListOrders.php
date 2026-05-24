<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Exports\OrdersExport;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
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
                ->action(function () {
                    return Excel::download(new OrdersExport(), 'orders-' . now()->format('Y-m-d') . '.xlsx');
                }),
        ];
    }
}
