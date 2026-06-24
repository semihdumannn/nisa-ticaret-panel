<?php

namespace App\Modules\Review\Presentation\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Review */
class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'order_id'   => $this->order_id,
            'product_id' => $this->product_id,
            'rating'     => $this->rating,
            'comment'    => $this->comment,
            'tags'       => $this->tags ?? [],
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private static function formatUserName(?string $name): string
    {
        if (! $name) {
            return '';
        }

        $parts = explode(' ', trim($name));

        if (count($parts) === 1) {
            return $parts[0];
        }

        $firstName = $parts[0];
        $lastInitial = mb_substr($parts[count($parts) - 1], 0, 1);

        return $firstName . ' ' . $lastInitial . '.';
    }

    public static function listItem(\App\Models\Review $review): array
    {
        return [
            'id'         => $review->id,
            'rating'     => $review->rating,
            'comment'    => $review->comment,
            'tags'       => $review->tags ?? [],
            'user_name'  => self::formatUserName($review->user?->name),
            'created_at' => $review->created_at?->toISOString(),
        ];
    }
}
