<?php

namespace App\Modules\Notification\Application\UseCases;

use App\Models\Notification;
use App\Modules\Notification\Application\DTOs\SendNotificationDTO;
use App\Modules\Notification\Domain\Contracts\NotificationRepositoryInterface;
use App\Modules\Notification\Infrastructure\Jobs\SendPushNotificationJob;

class SendNotificationUseCase
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notificationRepo,
    ) {}

    /**
     * Persist the notification to the DB and dispatch a push job to the queue.
     */
    public function execute(SendNotificationDTO $dto): Notification
    {
        $notification = $this->notificationRepo->create([
            'user_id' => $dto->userId,
            'type'    => $dto->type->value,
            'title'   => $dto->title,
            'body'    => $dto->body,
            'data'    => $dto->data,
            'is_read' => false,
        ]);

        SendPushNotificationJob::dispatch($dto->userId, $dto->title, $dto->body, $dto->data);

        return $notification;
    }
}
