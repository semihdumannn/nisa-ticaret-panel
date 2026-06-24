<?php

namespace App\Modules\Subscription\Application\UseCases;

use App\Models\Address;
use App\Models\ProductVariant;
use App\Models\Subscription;
use App\Modules\Subscription\Domain\Contracts\SubscriptionRepositoryInterface;
use App\Modules\Subscription\Domain\Exceptions\SubscriptionException;

class CreateSubscriptionUseCase
{
    const PLAN_DISCOUNTS = ['weekly' => 10.0, 'biweekly' => 8.0, 'monthly' => 5.0];

    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptions
    ) {}

    public function execute(int $userId, array $data): Subscription
    {
        // 1. Load variant — throw if missing
        $variant = ProductVariant::find($data['variant_id']);
        if ($variant === null) {
            throw new SubscriptionException('Variant not found.', 'VARIANT_NOT_FOUND');
        }

        // 2. Check variant is active
        if (! $variant->is_active) {
            throw new SubscriptionException('Variant is not active.', 'VARIANT_INACTIVE');
        }

        // 3. Validate address belongs to user
        $address = Address::where('id', $data['address_id'])->where('user_id', $userId)->first();
        if ($address === null) {
            throw new SubscriptionException('Address does not belong to you.', 'ADDRESS_NOT_YOURS');
        }

        // 4. start_date >= today
        $startDate = \Carbon\Carbon::parse($data['start_date'])->startOfDay();
        if ($startDate->lt(now()->startOfDay())) {
            throw new SubscriptionException('Start date must be today or in the future.', 'INVALID_START_DATE');
        }

        // 5. Set discount_rate from plan
        $plan = $data['plan'];
        $discountRate = self::PLAN_DISCOUNTS[$plan];

        // 6. Set next_order_date = start_date
        $nextOrderDate = $startDate->toDateString();

        return $this->subscriptions->create([
            'user_id'        => $userId,
            'product_id'     => $data['product_id'],
            'variant_id'     => $data['variant_id'],
            'quantity'       => $data['quantity'],
            'address_id'     => $data['address_id'],
            'plan'           => $plan,
            'discount_rate'  => $discountRate,
            'status'         => 'active',
            'next_order_date' => $nextOrderDate,
            'start_date'     => $startDate->toDateString(),
            'notes'          => $data['notes'] ?? null,
        ]);
    }
}
