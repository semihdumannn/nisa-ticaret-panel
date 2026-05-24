<?php

namespace App\Modules\Notification\Domain\Events;

use App\Models\Order;

class OrderPlacedEvent
{
    public function __construct(public readonly Order $order) {}
}
