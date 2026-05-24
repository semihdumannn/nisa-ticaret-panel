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
            title:  'Order Placed',
            body:   "Your order {$order->order_number} has been received and is being processed.",
            data:   ['order_id' => $order->id, 'order_number' => $order->order_number],
        ));
    }

    public function handleOrderStatusUpdated(OrderStatusUpdatedEvent $event): void
    {
        $order = $event->order;
        if (! $order->customer_id) {
            return;
        }

        $statusLabel = ucwords(str_replace('_', ' ', $event->newStatus));

        $body = match ($event->newStatus) {
            'confirmed'   => "Your order {$order->order_number} has been confirmed.",
            'preparing'   => "Your order {$order->order_number} is being prepared.",
            'on_the_way'  => "Your order {$order->order_number} is on the way!",
            'delivered'   => "Your order {$order->order_number} has been delivered. Enjoy!",
            default       => "Your order {$order->order_number} status changed to {$statusLabel}.",
        };

        $this->sendNotification->execute(new SendNotificationDTO(
            userId: $order->customer_id,
            type:   NotificationType::ORDER_UPDATE,
            title:  "Order {$statusLabel}",
            body:   $body,
            data:   [
                'order_id'       => $order->id,
                'order_number'   => $order->order_number,
                'status'         => $event->newStatus,
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
            title:  'Order Cancelled',
            body:   "Your order {$order->order_number} has been cancelled.",
            data:   ['order_id' => $order->id, 'order_number' => $order->order_number],
        ));
    }
}
