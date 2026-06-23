<?php

namespace App\Modules\Favorite\Presentation\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Favorite */
class FavoriteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'product_id' => $this->product_id,
            'created_at' => $this->created_at?->toISOString(),
            'product'    => $this->whenLoaded('product', fn () => [
                'id'            => $this->product->id,
                'name'          => $this->product->name,
                'image_url'     => $this->product->image_url ?? null,
                'primary_price' => (float) $this->product->price,
                'is_active'     => (bool) $this->product->is_active,
            ]),
        ];
    }
}
