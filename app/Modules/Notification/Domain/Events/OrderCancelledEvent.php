<?php

namespace App\Modules\Notification\Domain\Events;

use App\Models\Order;

class OrderCancelledEvent
{
    public function __construct(
        public readonly Order   $order,
        public readonly ?string $reason = null,
    ) {}
}
