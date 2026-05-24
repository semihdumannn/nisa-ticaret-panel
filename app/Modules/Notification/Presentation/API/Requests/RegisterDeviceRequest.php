<?php

namespace App\Modules\Notification\Presentation\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterDeviceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'token'    => ['required', 'string', 'max:4096'],
            'platform' => ['nullable', 'string', 'in:ios,android'],
        ];
    }
}
