<?php

namespace App\Modules\Product\Presentation\API\Requests;

use App\Modules\Product\Domain\ValueObjects\ProductUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route is already guarded by role:admin middleware
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:200'],
            'price'         => ['required', 'numeric', 'min:0'],
            'brand_id'      => ['nullable', 'integer', 'exists:brands,id'],
            'sku'           => ['nullable', 'string', 'max:50', 'unique:products,sku'],
            'description'   => ['nullable', 'string'],
            'barcode'       => ['nullable', 'string', 'max:50'],
            'unit'          => ['nullable', Rule::in(array_column(ProductUnit::cases(), 'value'))],
            'cost_price'    => ['nullable', 'numeric', 'min:0'],
            'tax_rate'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'min_order_qty' => ['nullable', 'integer', 'min:1'],
            'max_order_qty' => ['nullable', 'integer', 'min:1'],
            'is_featured'   => ['nullable', 'boolean'],
            'is_active'     => ['nullable', 'boolean'],
            'metadata'      => ['nullable', 'array'],
            'category_ids'  => ['nullable', 'array'],
            'category_ids.*'=> ['integer', 'exists:categories,id'],
        ];
    }
}
