<?php

namespace App\Modules\Notification\Domain\Events;

use App\Models\Order;

class OrderStatusUpdatedEvent
{
    public function __construct(
        public readonly Order  $order,
        public readonly string $previousStatus,
        public readonly string $newStatus,
    ) {}
}
