<?php

namespace App\Modules\Subscription\Application\UseCases;

use App\Models\Subscription;
use App\Modules\Subscription\Domain\Contracts\SubscriptionRepositoryInterface;
use App\Modules\Subscription\Domain\Exceptions\SubscriptionException;

class CancelSubscriptionUseCase
{
    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptions
    ) {}

    public function execute(int $id, int $userId): Subscription
    {
        // Only own subscription
        $sub = $this->subscriptions->findByIdAndUser($id, $userId);
        if ($sub === null) {
            throw new SubscriptionException('Subscription not found.', 'SUBSCRIPTION_NOT_FOUND');
        }

        // Set status = 'cancelled', cancelled_at = now(), next_order_date = today as placeholder
        return $this->subscriptions->update($sub, [
            'status'          => 'cancelled',
            'cancelled_at'    => now(),
            'next_order_date' => now()->toDateString(),
        ]);
    }
}
