<?php

namespace App\Modules\Subscription\Presentation\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan'        => ['nullable', 'in:weekly,biweekly,monthly'],
            'quantity'    => ['nullable', 'integer', 'min:1'],
            'address_id'  => ['nullable', 'integer', 'exists:addresses,id'],
            'status'      => ['nullable', 'in:active,paused'],
            'pause_until' => [
                'nullable',
                'required_if:status,paused',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:today',
                'before_or_equal:' . now()->addDays(90)->toDateString(),
            ],
            'notes'       => ['nullable', 'string', 'max:500'],
        ];
    }
}
