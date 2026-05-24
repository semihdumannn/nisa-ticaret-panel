<?php

namespace App\Modules\Inventory\Presentation\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Inventory */
class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'product_id'          => $this->product_id,
            'variant_id'          => $this->variant_id,
            'warehouse'           => new WarehouseResource($this->whenLoaded('warehouse')),
            'quantity'            => $this->quantity,
            'reserved_quantity'   => $this->reserved_quantity,
            'available_quantity'  => $this->availableQuantity(),
            'is_low_stock'        => $this->isLowStock(),
            'last_restock_date'   => $this->last_restock_date?->toIso8601String(),
        ];
    }
}
