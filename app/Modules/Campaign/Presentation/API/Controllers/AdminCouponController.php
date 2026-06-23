<?php

namespace App\Modules\Campaign\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Modules\Campaign\Presentation\API\Requests\StoreCouponRequest;
use App\Modules\Campaign\Presentation\API\Requests\UpdateCouponRequest;
use App\Modules\Campaign\Presentation\API\Resources\CouponResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCouponController extends Controller
{
    /**
     * GET /api/v1/admin/coupons
     * List all coupons (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 20), 100);

        $query = Coupon::query()->orderByDesc('created_at');

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $coupons = $query->paginate($perPage);

        return response()->json([
            'data' => CouponResource::collection($coupons),
            'meta' => [
                'current_page' => $coupons->currentPage(),
                'last_page'    => $coupons->lastPage(),
                'per_page'     => $coupons->perPage(),
                'total'        => $coupons->total(),
            ],
        ]);
    }

    /**
     * POST /api/v1/admin/coupons
     * Create a new coupon.
     */
    public function store(StoreCouponRequest $request): JsonResponse
    {
        $v = $request->validated();

        $coupon = Coupon::create([
            'code'                => strtoupper($v['code']),
            'type'                => $v['type'],
            'value'               => $v['discount_value'],
            'min_purchase_amount' => $v['min_order_amount'] ?? null,
            'max_discount_amount' => $v['max_discount_amount'] ?? null,
            'usage_limit'         => $v['usage_limit'] ?? null,
            'usage_count'         => 0,
            'user_specific'       => false,
            'is_active'           => $v['is_active'] ?? true,
            'start_date'          => isset($v['starts_at']) ? $v['starts_at'] : now(),
            'end_date'            => isset($v['expires_at']) ? $v['expires_at'] : now()->addYear(),
        ]);

        return response()->json(['data' => new CouponResource($coupon)], 201);
    }

    /**
     * PUT /api/v1/admin/coupons/{id}
     * Update an existing coupon.
     */
    public function update(UpdateCouponRequest $request, int $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);
        $v      = $request->validated();

        $fillable = [];

        if (isset($v['code'])) {
            $fillable['code'] = strtoupper($v['code']);
        }
        if (isset($v['type'])) {
            $fillable['type'] = $v['type'];
        }
        if (isset($v['discount_value'])) {
            $fillable['value'] = $v['discount_value'];
        }
        if (array_key_exists('min_order_amount', $v)) {
            $fillable['min_purchase_amount'] = $v['min_order_amount'];
        }
        if (array_key_exists('max_discount_amount', $v)) {
            $fillable['max_discount_amount'] = $v['max_discount_amount'];
        }
        if (array_key_exists('usage_limit', $v)) {
            $fillable['usage_limit'] = $v['usage_limit'];
        }
        if (isset($v['is_active'])) {
            $fillable['is_active'] = $v['is_active'];
        }
        if (isset($v['starts_at'])) {
            $fillable['start_date'] = $v['starts_at'];
        }
        if (isset($v['expires_at'])) {
            $fillable['end_date'] = $v['expires_at'];
        }

        $coupon->update($fillable);

        return response()->json(['data' => new CouponResource($coupon->fresh())]);
    }

    /**
     * DELETE /api/v1/admin/coupons/{id}
     * Soft-deactivate a coupon (set is_active = false).
     */
    public function destroy(int $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => false]);

        return response()->json(null, 204);
    }
}
