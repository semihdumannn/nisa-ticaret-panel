<?php

namespace App\Modules\Faq\Presentation\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category'  => 'required|string|max:100',
            'question'  => 'required|string',
            'answer'    => 'required|string',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ];
    }
}
