<?php

namespace App\Modules\Order\Presentation\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'product_id' => $this->product_id,
            'variant_id' => $this->variant_id,
            'product'    => [
                'name'  => $this->product?->name,
                'sku'   => $this->product?->sku,
                'image' => $this->product?->images()->where('is_primary', true)->value('image_url'),
            ],
            'variant'    => $this->variant ? ['name' => $this->variant->name] : null,
            'quantity'   => $this->quantity,
            'unit_price' => $this->unitPrice(),
            'line_total' => $this->lineTotal(),
        ];
    }
}
