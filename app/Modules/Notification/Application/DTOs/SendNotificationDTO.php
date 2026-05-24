<?php

namespace App\Modules\Notification\Application\DTOs;

use App\Modules\Notification\Domain\ValueObjects\NotificationType;

readonly class SendNotificationDTO
{
    public function __construct(
        public int              $userId,
        public NotificationType $type,
        public string           $title,
        public string           $body,
        public array            $data = [],
    ) {}
}
