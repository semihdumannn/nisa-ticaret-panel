<?php

namespace App\Modules\Campaign\Application\UseCases;

use App\Models\Coupon;
use App\Modules\Campaign\Application\DTOs\ApplyCouponDTO;
use App\Modules\Campaign\Domain\Contracts\CouponRepositoryInterface;
use App\Modules\Campaign\Domain\Exceptions\CouponMinPurchaseException;
use App\Modules\Campaign\Domain\Exceptions\CouponUsageLimitException;
use App\Modules\Campaign\Domain\Exceptions\InvalidCouponException;

class ValidateCouponUseCase
{
    public function __construct(private readonly CouponRepositoryInterface $coupons) {}

    /**
     * Validate a coupon against the given context and return it if valid.
     *
     * @throws InvalidCouponException
     * @throws CouponUsageLimitException
     * @throws CouponMinPurchaseException
     */
    public function execute(ApplyCouponDTO $dto): Coupon
    {
        $coupon = $this->coupons->findByCode($dto->code);

        if (! $coupon || ! $coupon->isCurrentlyActive()) {
            throw new InvalidCouponException();
        }

        if ($coupon->isUsageLimitReached()) {
            throw new CouponUsageLimitException();
        }

        // If user-specific, check this user hasn't already used it
        if ($coupon->user_specific && $this->coupons->hasUserUsed($coupon->id, $dto->userId)) {
            throw new InvalidCouponException('You have already used this coupon.');
        }

        if ($coupon->min_purchase_amount && $dto->subtotal < (float) $coupon->min_purchase_amount) {
            throw new CouponMinPurchaseException((float) $coupon->min_purchase_amount);
        }

        return $coupon;
    }
}
