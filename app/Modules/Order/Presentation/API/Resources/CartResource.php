<?php

namespace App\Modules\Order\Presentation\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items    = $this->items->loadMissing('product', 'variant');
        $subtotal = $items->sum(fn ($i) => $i->lineTotal());

        return [
            'id'          => $this->id,
            'item_count'  => $this->totalItems(),
            'subtotal'    => round($subtotal, 2),
            'items'       => CartItemResource::collection($items),
        ];
    }
}
