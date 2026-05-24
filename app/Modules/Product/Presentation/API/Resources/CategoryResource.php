<?php

namespace App\Modules\Product\Presentation\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Category */
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'parent_id'      => $this->parent_id,
            'icon'           => $this->icon,
            'color'          => $this->color,
            'description'    => $this->description,
            'is_active'      => $this->is_active,
            'sort_order'     => $this->sort_order,
            'products_count' => $this->whenCounted('products'),
            'children'       => CategoryResource::collection($this->whenLoaded('childrenRecursive')),
        ];
    }
}
