<?php

namespace App\Modules\Campaign\Presentation\API\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $couponId = $this->route('id');

        return [
            'code'                   => ['sometimes', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($couponId)],
            'type'                   => ['sometimes', 'string', 'in:percentage,fixed_amount'],
            'discount_value'         => ['sometimes', 'numeric', 'min:0'],
            'min_order_amount'       => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'max_discount_amount'    => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'usage_limit'            => ['sometimes', 'nullable', 'integer', 'min:1'],
            'usage_limit_per_user'   => ['sometimes', 'nullable', 'integer', 'min:1'],
            'is_active'              => ['sometimes', 'boolean'],
            'starts_at'              => ['sometimes', 'nullable', 'date'],
            'expires_at'             => ['sometimes', 'nullable', 'date', 'after:starts_at'],
            'description'            => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
