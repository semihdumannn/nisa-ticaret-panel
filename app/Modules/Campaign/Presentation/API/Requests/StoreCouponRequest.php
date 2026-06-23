<?php

namespace App\Modules\Campaign\Presentation\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'                   => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'type'                   => ['required', 'string', 'in:percentage,fixed_amount'],
            'discount_value'         => ['required', 'numeric', 'min:0'],
            'min_order_amount'       => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount'    => ['nullable', 'numeric', 'min:0'],
            'usage_limit'            => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_user'   => ['nullable', 'integer', 'min:1'],
            'is_active'              => ['boolean'],
            'starts_at'              => ['nullable', 'date'],
            'expires_at'             => ['nullable', 'date', 'after:starts_at'],
            'description'            => ['nullable', 'string', 'max:255'],
        ];
    }
}
