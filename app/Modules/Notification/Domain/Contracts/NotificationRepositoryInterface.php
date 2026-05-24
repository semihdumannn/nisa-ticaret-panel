<?php

namespace App\Modules\Notification\Domain\Contracts;

use App\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface
{
    public function create(array $data): Notification;

    /** Paginated list for a specific user, newest first. */
    public function forUser(int $userId, int $perPage = 20): LengthAwarePaginator;

    /** Count unread notifications for a user. */
    public function unreadCount(int $userId): int;

    /** Mark all of a user's notifications as read. */
    public function markAllRead(int $userId): void;

    /** Mark specific notification IDs as read (scoped to user). */
    public function markRead(int $userId, array $ids): void;

    public function delete(int $notificationId, int $userId): bool;
}
