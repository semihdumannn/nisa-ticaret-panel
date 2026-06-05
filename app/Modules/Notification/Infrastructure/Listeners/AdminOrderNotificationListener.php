<?php

namespace App\Modules\Notification\Infrastructure\Listeners;

use App\Models\User;
use App\Modules\Notification\Domain\Events\OrderPlacedEvent;
use App\Modules\Notification\Domain\Events\OrderStatusUpdatedEvent;
use App\Modules\Notification\Infrastructure\Jobs\SendPushNotificationJob;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

/**
 * Sends Filament database notifications to all admin users
 * when order lifecycle events occur.
 */
class AdminOrderNotificationListener
{
    public function handleOrderPlaced(OrderPlacedEvent $event): void
    {
        $order  = $event->order;
        $admins = User::where('role', 'admin')->where('is_active', true)->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::make()
            ->title('Yeni Sipariş Alındı 🛒')
            ->body("Sipariş {$order->order_number} — ₺" . number_format((float) $order->total, 2) . ' · ' . ($order->customer?->name ?? 'Misafir'))
            ->icon('heroicon-o-shopping-cart')
            ->iconColor('primary')
            ->actions([
                Action::make('view')
                    ->label('Siparişi Görüntüle')
                    ->url(url("/admin/orders/{$order->id}"))
                    ->button(),
            ])
            ->sendToDatabase($admins);

        foreach ($admins as $admin) {
            SendPushNotificationJob::dispatch(
                $admin->id,
                'Yeni Sipariş 🛒',
                "Sipariş {$order->order_number} — ₺" . number_format((float) $order->total, 2),
                ['type' => 'new_order', 'order_id' => (string) $order->id],
            );
        }
    }

    public function handleOrderStatusUpdated(OrderStatusUpdatedEvent $event): void
    {
        $order  = $event->order;
        $status = OrderStatus::tryFrom($event->newStatus);

        // Only notify admins for terminal statuses (delivered/cancelled)
        if (! $status?->isTerminal()) {
            return;
        }

        $admins = User::where('role', 'admin')->where('is_active', true)->get();

        if ($admins->isEmpty()) {
            return;
        }

        $isDelivered = $status === OrderStatus::DELIVERED;

        Notification::make()
            ->title($isDelivered ? 'Sipariş Teslim Edildi ✅' : 'Sipariş İptal Edildi ❌')
            ->body("Sipariş {$order->order_number} " . ($isDelivered ? 'teslim edildi.' : 'iptal edildi.'))
            ->icon($isDelivered ? 'heroicon-o-check-badge' : 'heroicon-o-x-circle')
            ->iconColor($isDelivered ? 'success' : 'danger')
            ->sendToDatabase($admins);
    }
}
