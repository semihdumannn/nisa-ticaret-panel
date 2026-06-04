<?php

namespace App\Modules\User\Presentation\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'role'       => $this->role,
            'is_active'  => $this->is_active,
            'profile'    => $this->whenLoaded('profile', fn () => [
                'avatar_url'   => $this->profile?->avatar_url,
                'company_name' => $this->profile?->company_name,
                'tax_number'   => $this->profile?->tax_number,
                'balance'      => (float) ($this->profile?->balance ?? 0),
                'credit_limit' => (float) ($this->profile?->credit_limit ?? 0),
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
