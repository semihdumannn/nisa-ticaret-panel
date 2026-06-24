<?php

namespace App\Modules\Subscription\Application\UseCases;

use App\Models\Subscription;
use App\Modules\Subscription\Domain\Contracts\SubscriptionRepositoryInterface;
use App\Modules\Subscription\Domain\Exceptions\SubscriptionException;

class UpdateSubscriptionUseCase
{
    const PLAN_DISCOUNTS = ['weekly' => 10.0, 'biweekly' => 8.0, 'monthly' => 5.0];

    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptions
    ) {}

    public function execute(int $id, int $userId, array $data): Subscription
    {
        // Only own subscription
        $sub = $this->subscriptions->findByIdAndUser($id, $userId);
        if ($sub === null) {
            throw new SubscriptionException('Subscription not found.', 'SUBSCRIPTION_NOT_FOUND');
        }

        // Not cancelled
        if ($sub->status === 'cancelled') {
            throw new SubscriptionException('Cannot update a cancelled subscription.', 'SUBSCRIPTION_CANCELLED');
        }

        $updateData = [];

        // Do NOT allow changing product_id or variant_id — silently ignore
        unset($data['product_id'], $data['variant_id']);

        // If plan changes: update discount_rate automatically
        if (isset($data['plan'])) {
            $updateData['plan'] = $data['plan'];
            $updateData['discount_rate'] = self::PLAN_DISCOUNTS[$data['plan']];
        }

        if (isset($data['quantity'])) {
            $updateData['quantity'] = $data['quantity'];
        }

        if (isset($data['address_id'])) {
            $updateData['address_id'] = $data['address_id'];
        }

        if (isset($data['notes'])) {
            $updateData['notes'] = $data['notes'];
        }

        if (isset($data['status'])) {
            $newStatus = $data['status'];

            if ($newStatus === 'paused') {
                // pause_until is validated in the Request, so it's present
                $updateData['status'] = 'paused';
                $updateData['pause_until'] = $data['pause_until'];
            } elseif ($newStatus === 'active') {
                // Clear pause_until, recalculate next_order_date from today
                $updateData['status'] = 'active';
                $updateData['pause_until'] = null;
                $updateData['next_order_date'] = now()->toDateString();
            }
        }

        return $this->subscriptions->update($sub, $updateData);
    }
}
