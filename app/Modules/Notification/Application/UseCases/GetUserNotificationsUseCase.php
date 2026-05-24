<?php

namespace App\Modules\Notification\Application\UseCases;

use App\Modules\Notification\Domain\Contracts\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetUserNotificationsUseCase
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notificationRepo,
    ) {}

    public function execute(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->notificationRepo->forUser($userId, $perPage);
    }
}
