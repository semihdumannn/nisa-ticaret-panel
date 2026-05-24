<?php

namespace App\Modules\Order\Presentation\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:0', 'max:999'],
        ];
    }
}
