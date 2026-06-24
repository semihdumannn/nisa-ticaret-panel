<?php

namespace App\Modules\Review\Presentation\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id'   => ['required', 'integer', 'exists:orders,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'rating'     => ['required', 'integer', 'between:1,5'],
            'comment'    => ['nullable', 'string', 'max:1000'],
            'tags'       => ['nullable', 'array', 'max:5'],
            'tags.*'     => ['string', 'max:50'],
        ];
    }
}
