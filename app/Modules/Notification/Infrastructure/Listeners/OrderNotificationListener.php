<?php

namespace App\Modules\Notification\Infrastructure\Listeners;

use App\Modules\Notification\Application\DTOs\SendNotificationDTO;
use App\Modules\Notification\Application\UseCases\SendNotificationUseCase;
use App\Modules\Notification\Domain\Events\OrderCancelledEvent;
use App\Modules\Notification\Domain\Events\OrderPlacedEvent;
use App\Modules\Notification\Domain\Events\OrderStatusUpdatedEvent;
use App\Modules\Notification\Domain\ValueObjects\NotificationType;

class OrderNotificationListener
{
    public function __construct(private readonly SendNotificationUseCase $sendNotification) {}

    public function handleOrderPlaced(OrderPlacedEvent $event): void
    {
        $order = $event->order;
        if (! $order->customer_id) {
            return;
        }

        $this->sendNotification->execute(new SendNotificationDTO(
            userId: $order->customer_id,
            type:   NotificationType::ORDER_UPDATE,
            title:  'Siparişiniz Alındı',
            body:   "Siparişiniz {$order->order_number} alındı, işleme koyuldu.",
            data:   [
                'order_id'     => (string) $order->id,
                'order_number' => (string) $order->order_number,
                'type'         => 'order_placed',
            ],
        ));
    }

    public function handleOrderStatusUpdated(OrderStatusUpdatedEvent $event): void
    {
        $order = $event->order;
        if (! $order->customer_id) {
            return;
        }

        [$title, $body] = match ($event->newStatus) {
            'confirmed'  => ['Siparişiniz Onaylandı ✅', "Siparişiniz {$order->order_number} onaylandı."],
            'preparing'  => ['Siparişiniz Hazırlanıyor 🛒', "Siparişiniz {$order->order_number} hazırlanıyor."],
            'on_the_way' => ['Siparişiniz Yolda 🚚', "Siparişiniz {$order->order_number} yola çıktı!"],
            'delivered'  => ['Siparişiniz Teslim Edildi 🎉', "Siparişiniz {$order->order_number} teslim edildi."],
            default      => ['Sipariş Güncellendi', "Siparişiniz {$order->order_number} durumu güncellendi."],
        };

        $this->sendNotification->execute(new SendNotificationDTO(
            userId: $order->customer_id,
            type:   NotificationType::ORDER_UPDATE,
            title:  $title,
            body:   $body,
            data:   [
                'order_id'     => (string) $order->id,
                'order_number' => (string) $order->order_number,
                'status'       => $event->newStatus,
                'type'         => 'order_status_updated',
            ],
        ));
    }

    public function handleOrderCancelled(OrderCancelledEvent $event): void
    {
        $order = $event->order;
        if (! $order->customer_id) {
            return;
        }

        $this->sendNotification->execute(new SendNotificationDTO(
            userId: $order->customer_id,
            type:   NotificationType::ORDER_UPDATE,
            title:  'Siparişiniz İptal Edildi',
            body:   "Siparişiniz {$order->order_number} iptal edildi.",
            data:   [
                'order_id'     => (string) $order->id,
                'order_number' => (string) $order->order_number,
                'type'         => 'order_cancelled',
            ],
        ));
    }
}
