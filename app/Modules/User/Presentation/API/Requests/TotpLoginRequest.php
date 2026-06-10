<?php

namespace App\Modules\User\Presentation\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TotpLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone'       => ['required', 'string', 'max:20'],
            'code'        => ['required', 'string', 'size:6'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Telefon numarası gereklidir.',
            'code.required'  => 'Doğrulama kodu gereklidir.',
            'code.size'      => 'Doğrulama kodu 6 haneli olmalıdır.',
        ];
    }
}
