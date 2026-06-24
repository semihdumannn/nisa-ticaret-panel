<?php

namespace App\Modules\Subscription\Application\UseCases;

use App\Modules\Subscription\Domain\Contracts\SubscriptionRepositoryInterface;
use Illuminate\Support\Collection;

class ListSubscriptionsUseCase
{
    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptions
    ) {}

    public function execute(int $userId, array $statuses): Collection
    {
        return $this->subscriptions->listForUser($userId, $statuses);
    }
}
