<?php

namespace App\Modules\Order\Presentation\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'address_id'         => ['required', 'integer', 'exists:addresses,id'],
            'payment_method'     => ['nullable', 'string', 'in:cash,credit_card,bank_transfer,account'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'coupon_code'        => ['nullable', 'string', 'max:50'],
            'items'              => ['sometimes', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity'   => ['required_with:items', 'integer', 'min:1'],
        ];
    }
}
