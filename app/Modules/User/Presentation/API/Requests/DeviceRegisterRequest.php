<?php

namespace App\Modules\User\Presentation\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeviceRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone'       => ['required', 'string', 'max:20'],
            'name'        => ['nullable', 'string', 'max:100'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Telefon numarası gereklidir.',
        ];
    }
}
