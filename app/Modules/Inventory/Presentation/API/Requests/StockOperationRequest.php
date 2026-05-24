<?php

namespace App\Modules\Inventory\Presentation\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockOperationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'product_id'   => ['required', 'integer', 'exists:products,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'quantity'     => ['required', 'integer', 'min:1'],
            'variant_id'   => ['nullable', 'integer', 'exists:product_variants,id'],
            'reason'       => ['nullable', 'string', 'max:255'],
        ];
    }
}
