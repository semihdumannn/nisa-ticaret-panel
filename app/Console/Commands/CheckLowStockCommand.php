<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class CheckLowStockCommand extends Command
{
    protected $signature   = 'inventory:check-low-stock
                              {--threshold=5 : Quantity threshold for low stock alert}';

    protected $description = 'Check for low-stock products and notify admins via Filament database notifications.';

    public function handle(): int
    {
        $threshold = (int) $this->option('threshold');

        $lowStock = Inventory::with(['product', 'warehouse'])
            ->whereRaw('(quantity - reserved_quantity) <= ?', [$threshold])
            ->where('quantity', '>', 0)
            ->get()
            ->groupBy('product_id');

        if ($lowStock->isEmpty()) {
            $this->info('No low-stock items found.');
            return self::SUCCESS;
        }

        $admins = User::where('role', 'admin')->where('is_active', true)->get();

        if ($admins->isEmpty()) {
            $this->warn('No active admin users to notify.');
            return self::SUCCESS;
        }

        $count = $lowStock->count();

        $productList = $lowStock
            ->take(5)
            ->map(fn ($rows) => $rows->first()->product?->name ?? 'Unknown')
            ->join(', ');

        $body = $count > 5
            ? "{$productList} and " . ($count - 5) . " more product(s) are running low."
            : "{$productList}";

        Notification::make()
            ->title("⚠️ {$count} Product(s) Running Low on Stock")
            ->body($body)
            ->icon('heroicon-o-exclamation-triangle')
            ->iconColor('warning')
            ->actions([
                Action::make('view_inventory')
                    ->label('View Inventory')
                    ->url(fn () => route('filament.admin.resources.inventories.index'))
                    ->button(),
            ])
            ->sendToDatabase($admins);

        $this->info("Low-stock notification sent to {$admins->count()} admin(s). Products affected: {$count}.");

        return self::SUCCESS;
    }
}
