<?php

namespace App\Modules\Product\Presentation\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'sku'             => $this->sku,
            'name'            => $this->name,
            'slug'            => $this->slug,
            'description'     => $this->description,
            'barcode'         => $this->barcode,
            'unit'            => $this->unit,
            'price'           => (float) $this->price,
            'price_with_tax'  => $this->priceWithTax(),
            'tax_rate'        => (float) $this->tax_rate,
            'min_order_qty'   => $this->min_order_qty,
            'max_order_qty'   => $this->max_order_qty,
            'is_featured'     => $this->is_featured,
            'is_active'       => $this->is_active,
            'metadata'        => $this->metadata,
            'brand'           => new BrandResource($this->whenLoaded('brand')),
            'categories'      => CategoryResource::collection($this->whenLoaded('categories')),
            'images'          => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id'         => $img->id,
                'url'        => $img->image_url,
                'is_primary' => $img->is_primary,
                'sort_order' => $img->sort_order,
            ])),
            'primary_image'   => $this->when(
                $this->relationLoaded('images'),
                fn () => $this->images->where('is_primary', true)->first()?->image_url,
            ),
            'variants'        => $this->whenLoaded('variants', fn () => $this->variants->map(fn ($v) => [
                'id'               => $v->id,
                'sku'              => $v->sku,
                'name'             => $v->name,
                'attributes'       => $v->attributes,
                'price_adjustment' => (float) $v->price_adjustment,
                'effective_price'  => $v->effectivePrice(),
                'stock'            => $v->stock,
                'is_active'        => $v->is_active,
            ])),
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
