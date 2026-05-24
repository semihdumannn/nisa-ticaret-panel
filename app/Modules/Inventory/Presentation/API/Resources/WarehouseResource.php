<?php

namespace App\Modules\Inventory\Presentation\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Warehouse */
class WarehouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'code'      => $this->code,
            'address'   => $this->address,
            'city'      => $this->city,
            'is_active' => $this->is_active,
        ];
    }
}
