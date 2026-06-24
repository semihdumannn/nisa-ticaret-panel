<?php

namespace App\Modules\Subscription\Presentation\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'plan'       => ['required', 'in:weekly,biweekly,monthly'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ];
    }
}
