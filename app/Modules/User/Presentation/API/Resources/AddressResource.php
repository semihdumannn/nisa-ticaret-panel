<?php

namespace App\Modules\User\Presentation\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Address */
class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'full_address' => $this->full_address,
            'district'     => $this->district,
            'city'         => $this->city,
            'postal_code'  => $this->postal_code,
            'latitude'     => $this->latitude,
            'longitude'    => $this->longitude,
            'is_default'   => $this->is_default,
            'created_at'   => $this->created_at?->toIso8601String(),
        ];
    }
}
