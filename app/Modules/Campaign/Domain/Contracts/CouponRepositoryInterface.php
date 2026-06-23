<?php

namespace App\Modules\Campaign\Domain\Contracts;

use App\Models\Coupon;
use Illuminate\Support\Collection;

interface CouponRepositoryInterface
{
    public function findByCode(string $code): ?Coupon;

    /** Record that a user used a coupon on a specific order. */
    public function recordUsage(int $couponId, int $userId, int $orderId): void;

    public function incrementUsage(int $couponId): void;

    /** Has this user already used this coupon? */
    public function hasUserUsed(int $couponId, int $userId): bool;

    /** List coupons that are currently active (not expired, usage limit not reached). */
    public function listActive(): Collection;
}
