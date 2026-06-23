<?php

namespace App\Modules\Campaign\Presentation\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'code'                => $this->code,
            'type'                => $this->type,
            'type_label'          => $this->couponType()->label(),
            'value'               => (float) $this->value,
            'discount_value'      => (float) $this->value,
            'min_purchase_amount' => $this->min_purchase_amount ? (float) $this->min_purchase_amount : null,
            'max_discount_amount' => $this->max_discount_amount ? (float) $this->max_discount_amount : null,
            'is_active'           => $this->is_active,
            'is_currently_active' => $this->isCurrentlyActive(),
            'usage_limit'         => $this->usage_limit,
            'usage_count'         => $this->usage_count,
            'start_date'          => $this->start_date?->toIso8601String(),
            'end_date'            => $this->end_date?->toIso8601String(),
        ];
    }
}
