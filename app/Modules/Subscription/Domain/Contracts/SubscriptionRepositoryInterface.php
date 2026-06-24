<?php

namespace App\Modules\Subscription\Domain\Contracts;

use App\Models\Subscription;
use Illuminate\Support\Collection;

interface SubscriptionRepositoryInterface
{
    public function create(array $data): Subscription;

    public function update(Subscription $sub, array $data): Subscription;

    public function findByIdAndUser(int $id, int $userId): ?Subscription;

    public function listForUser(int $userId, array $statuses): Collection;

    /** For cron job (Task 5) — returns all active subscriptions due today. */
    public function findDueToday(): Collection;
}
