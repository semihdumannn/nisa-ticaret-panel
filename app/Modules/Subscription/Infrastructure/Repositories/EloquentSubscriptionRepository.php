<?php

namespace App\Modules\Subscription\Infrastructure\Repositories;

use App\Models\Subscription;
use App\Modules\Subscription\Domain\Contracts\SubscriptionRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentSubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function create(array $data): Subscription
    {
        return Subscription::create($data);
    }

    public function update(Subscription $sub, array $data): Subscription
    {
        $sub->update($data);
        $sub->refresh();

        return $sub;
    }

    public function findByIdAndUser(int $id, int $userId): ?Subscription
    {
        return Subscription::where('id', $id)
            ->where('user_id', $userId)
            ->with(['product', 'variant.product', 'address'])
            ->first();
    }

    public function listForUser(int $userId, array $statuses): Collection
    {
        return Subscription::where('user_id', $userId)
            ->whereIn('status', $statuses)
            ->with(['product', 'variant.product', 'address'])
            ->latest('created_at')
            ->get();
    }

    /**
     * Find all active subscriptions due today (for cron job — Task 5).
     * Returns active subscriptions where next_order_date <= today.
     * Uses whereDate() for cross-database compatibility (SQLite stores dates with time component).
     */
    public function findDueToday(): Collection
    {
        return Subscription::where('status', 'active')
            ->whereDate('next_order_date', '<=', now()->toDateString())
            ->with(['user', 'product', 'variant.product', 'address'])
            ->get();
    }
}
