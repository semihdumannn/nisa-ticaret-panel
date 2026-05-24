<?php

namespace App\Modules\Product\Presentation\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Brand */
class BrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'logo_url'       => $this->logo_url,
            'description'    => $this->description,
            'is_active'      => $this->is_active,
            'sort_order'     => $this->sort_order,
            'products_count' => $this->whenCounted('products'),
        ];
    }
}
