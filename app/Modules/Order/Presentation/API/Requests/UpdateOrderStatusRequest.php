<?php

namespace App\Modules\Order\Presentation\API\Requests;

use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::enum(OrderStatus::class)],
            'note'   => ['nullable', 'string', 'max:500'],
        ];
    }
}
