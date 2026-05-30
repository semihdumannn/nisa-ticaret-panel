<?php

namespace App\Modules\Product\Presentation\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $stockQty = max(
            0,
            (int) ($this->total_quantity ?? 0) - (int) ($this->total_reserved ?? 0)
        );

        // sale_price stored in product metadata (no dedicated column)
        $salePrice = isset($this->metadata['sale_price'])
            ? (float) $this->metadata['sale_price']
            : null;

        $koliVariant = $this->relationLoaded('variants')
            ? $this->variants->first(fn ($v) => $v->is_koli)
            : null;

        return [
            'id'             => $this->id,
            'sku'            => $this->sku,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'description'    => $this->description,
            'barcode'        => $this->barcode,
            'unit'           => $this->unit,
            'price'          => (float) $this->price,
            'sale_price'     => $salePrice,
            'price_with_tax' => $this->priceWithTax(),
            'tax_rate'       => (float) $this->tax_rate,
            'stock_quantity' => $stockQty,
            'min_order_qty'  => $this->min_order_qty,
            'max_order_qty'  => $this->max_order_qty,
            'is_featured'    => $this->is_featured,
            'is_active'      => $this->is_active,

            // Koli convenience fields (derived from the koli variant)
            'koli_variant_id'  => $koliVariant?->id,
            'koli_price'       => $koliVariant
                ? round((float) $this->price + (float) $koliVariant->price_adjustment, 2)
                : null,
            'koli_sale_price'  => $koliVariant?->sale_price,
            'koli_stock'       => $koliVariant !== null ? (int) $koliVariant->stock : null,
            'koli_package_qty' => $koliVariant?->package_qty,

            'brand'      => new BrandResource($this->whenLoaded('brand')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'images'     => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id'         => $img->id,
                'url'        => $img->image_url,
                'is_primary' => $img->is_primary,
                'sort_order' => $img->sort_order,
            ])),
            'primary_image' => $this->when(
                $this->relationLoaded('images'),
                fn () => $this->images->where('is_primary', true)->first()?->image_url,
            ),
            'variants' => $this->whenLoaded('variants', fn () => $this->variants->map(fn ($v) => [
                'id'            => $v->id,
                'sku'           => $v->sku,
                'name'          => $v->name,
                'price'         => round((float) $this->price + (float) $v->price_adjustment, 2),
                'sale_price'    => $v->sale_price,
                'unit'          => $v->unit ?? $this->unit,
                'min_order_qty' => $v->min_order_qty ?? (int) $this->min_order_qty,
                'max_order_qty' => $v->max_order_qty ?? $this->max_order_qty,
                'package_qty'   => $v->package_qty,
                'is_koli'       => $v->is_koli,
                'stock'         => (int) $v->stock,
                'is_active'     => $v->is_active,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
