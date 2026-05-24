<?php

namespace App\Modules\Inventory\Presentation\API\Resources;

use App\Modules\Inventory\Domain\ValueObjects\MovementType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\StockMovement */
class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'type'           => $this->type,
            'type_label'     => MovementType::tryFrom($this->type)?->label() ?? $this->type,
            'quantity'       => $this->quantity,
            'reason'         => $this->reason,
            'reference_type' => $this->reference_type,
            'reference_id'   => $this->reference_id,
            'product'        => $this->whenLoaded('product', fn () => [
                'id'   => $this->product?->id,
                'name' => $this->product?->name,
                'sku'  => $this->product?->sku,
            ]),
            'warehouse'      => $this->whenLoaded('warehouse', fn () => [
                'id'   => $this->warehouse?->id,
                'name' => $this->warehouse?->name,
                'code' => $this->warehouse?->code,
            ]),
            'user'           => $this->whenLoaded('user', fn () => [
                'id'   => $this->user?->id,
                'name' => $this->user?->name,
            ]),
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}
