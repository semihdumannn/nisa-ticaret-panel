<?php

namespace App\Modules\User\Presentation\API\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name'         => ['nullable', 'string', 'max:100'],
            'email'        => ['nullable', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'company_name' => ['nullable', 'string', 'max:200'],
            'tax_number'   => ['nullable', 'string', 'max:20'],
        ];
    }
}
