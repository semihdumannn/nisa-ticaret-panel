<?php

namespace App\Modules\Notification\Application\UseCases;

use App\Modules\Notification\Domain\Contracts\NotificationRepositoryInterface;

class MarkNotificationsReadUseCase
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notificationRepo,
    ) {}

    /**
     * Mark notifications as read.
     * If $ids is empty, marks ALL of the user's notifications.
     *
     * @param  int[]  $ids
     */
    public function execute(int $userId, array $ids = []): void
    {
        if (empty($ids)) {
            $this->notificationRepo->markAllRead($userId);
        } else {
            $this->notificationRepo->markRead($userId, $ids);
        }
    }
}
