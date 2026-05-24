<?php

namespace App\Modules\Campaign\Infrastructure\Repositories;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Modules\Campaign\Domain\Contracts\CouponRepositoryInterface;

class EloquentCouponRepository implements CouponRepositoryInterface
{
    public function findByCode(string $code): ?Coupon
    {
        return Coupon::where('code', strtoupper(trim($code)))->first();
    }

    public function recordUsage(int $couponId, int $userId, int $orderId): void
    {
        CouponUsage::create([
            'coupon_id' => $couponId,
            'user_id'   => $userId,
            'order_id'  => $orderId,
        ]);
    }

    public function incrementUsage(int $couponId): void
    {
        Coupon::where('id', $couponId)->increment('usage_count');
    }

    public function hasUserUsed(int $couponId, int $userId): bool
    {
        return CouponUsage::where('coupon_id', $couponId)
            ->where('user_id', $userId)
            ->exists();
    }
}
