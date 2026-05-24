<?php

namespace App\Modules\Order\Presentation\API\Resources;

use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use App\Modules\Order\Domain\ValueObjects\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status  = OrderStatus::from($this->status);
        $payment = PaymentStatus::from($this->payment_status);

        return [
            'id'              => $this->id,
            'order_number'    => $this->order_number,
            'status'          => $this->status,
            'status_label'    => $status->label(),
            'status_color'    => $status->color(),
            'payment_method'  => $this->payment_method,
            'payment_status'  => $this->payment_status,
            'payment_label'   => $payment->label(),
            'subtotal'        => (float) $this->subtotal,
            'discount_amount' => (float) $this->discount_amount,
            'tax_amount'      => (float) $this->tax_amount,
            'shipping_amount' => (float) $this->shipping_amount,
            'total'           => (float) $this->total,
            'notes'           => $this->notes,
            'delivered_at'    => $this->delivered_at?->toDateTimeString(),
            'created_at'      => $this->created_at->toDateTimeString(),
            'address'         => $this->whenLoaded('address', fn () => [
                'title'        => $this->address->title,
                'full_address' => $this->address->full_address,
                'city'         => $this->address->city,
            ]),
            'items'           => OrderItemResource::collection($this->whenLoaded('items')),
            'can_cancel'      => $this->canTransitionTo(
                \App\Modules\Order\Domain\ValueObjects\OrderStatus::CANCELLED
            ),
        ];
    }
}
