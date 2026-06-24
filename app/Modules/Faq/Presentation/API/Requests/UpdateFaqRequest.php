<?php

namespace App\Modules\Faq\Presentation\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category'   => 'sometimes|string|max:100',
            'question'   => 'sometimes|string',
            'answer'     => 'sometimes|string',
            'sort_order' => 'sometimes|integer|min:0',
            'is_active'  => 'sometimes|boolean',
        ];
    }
}
