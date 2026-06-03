<?php

namespace App\Modules\Notification\Infrastructure\Listeners;

use App\Models\User;
use App\Modules\Notification\Domain\Events\OrderPlacedEvent;
use App\Modules\Notification\Domain\Events\OrderStatusUpdatedEvent;
use App\Modules\Notification\Infrastructure\Jobs\SendPushNotificationJob;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Filament\Actions\Action;
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
            ->title('New Order Received')
            ->body("Order {$order->order_number} — ₺" . number_format((float) $order->total, 2) . ' from ' . ($order->customer?->name ?? 'Unknown'))
            ->icon('heroicon-o-shopping-cart')
            ->iconColor('primary')
            ->actions([
                Action::make('view')
                    ->label('View Order')
                    ->url(\App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $order->id]))
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
            ->title($isDelivered ? 'Order Delivered' : 'Order Cancelled')
            ->body("Order {$order->order_number} has been {$status->label()}.")
            ->icon($isDelivered ? 'heroicon-o-check-badge' : 'heroicon-o-x-circle')
            ->iconColor($isDelivered ? 'success' : 'danger')
            ->sendToDatabase($admins);
    }
}
