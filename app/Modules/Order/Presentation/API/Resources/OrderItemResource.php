<?php

namespace App\Modules\Order\Presentation\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'product_id'      => $this->product_id,
            'variant_id'      => $this->variant_id,
            'product_name'    => $this->product_name,
            'quantity'        => $this->quantity,
            'unit_price'      => (float) $this->unit_price,
            'tax_rate'        => (float) $this->tax_rate,
            'discount_amount' => (float) $this->discount_amount,
            'total'           => (float) $this->total,
        ];
    }
}
