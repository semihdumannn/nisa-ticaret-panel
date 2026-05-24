<?php

namespace App\Modules\Campaign\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Campaign\Application\DTOs\ApplyCouponDTO;
use App\Modules\Campaign\Application\UseCases\ValidateCouponUseCase;
use App\Modules\Campaign\Domain\Exceptions\CouponMinPurchaseException;
use App\Modules\Campaign\Domain\Exceptions\CouponUsageLimitException;
use App\Modules\Campaign\Domain\Exceptions\InvalidCouponException;
use App\Modules\Campaign\Presentation\API\Requests\ApplyCouponRequest;
use App\Modules\Campaign\Presentation\API\Resources\CouponResource;
use Illuminate\Http\JsonResponse;

class CouponController extends Controller
{
    /**
     * POST /api/v1/coupons/validate
     * Validate a coupon code for the authenticated user's cart subtotal.
     */
    public function validate(ApplyCouponRequest $request, ValidateCouponUseCase $useCase): JsonResponse
    {
        $v = $request->validated();

        try {
            $coupon   = $useCase->execute(new ApplyCouponDTO(
                code:     $v['code'],
                userId:   $request->user()->id,
                subtotal: (float) $v['subtotal'],
            ));
            $discount = $coupon->calculateDiscount((float) $v['subtotal']);

            return response()->json([
                'data'     => new CouponResource($coupon),
                'discount' => $discount,
            ]);
        } catch (InvalidCouponException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (CouponUsageLimitException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (CouponMinPurchaseException $e) {
            return response()->json([
                'message'    => $e->getMessage(),
                'min_amount' => $e->minAmount,
            ], 422);
        }
    }
}
