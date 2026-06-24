<?php

namespace App\Modules\Subscription\Presentation\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'plan'             => $this->plan,
            'quantity'         => $this->quantity,
            'discount_rate'    => (float) $this->discount_rate,
            'discounted_price' => $this->whenLoaded('variant', function () {
                $base = (float) $this->variant->effectivePrice();
                return round($base * $this->quantity * (1 - $this->discount_rate / 100), 2);
            }),
            'status'           => $this->status,
            'next_order_date'  => $this->next_order_date?->toDateString(),
            'pause_until'      => $this->pause_until?->toDateString(),
            'start_date'       => $this->start_date?->toDateString(),
            'notes'            => $this->notes,
            'created_at'       => $this->created_at?->toISOString(),
            'product'          => $this->whenLoaded('product', fn () => [
                'id'        => $this->product->id,
                'name'      => $this->product->name,
                'image_url' => $this->product->image_url ?? null,
            ]),
            'variant'          => $this->whenLoaded('variant', fn () => [
                'id'    => $this->variant->id,
                'name'  => $this->variant->name,
                'price' => (float) $this->variant->effectivePrice(),
            ]),
            'address'          => $this->whenLoaded('address', fn () => [
                'id'           => $this->address->id,
                'title'        => $this->address->title,
                'full_address' => $this->address->full_address,
            ]),
        ];
    }
}
