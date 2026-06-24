<?php

namespace App\Modules\Faq\Presentation\API\Controllers;

use App\Models\FaqItem;

class FaqController
{
    public function index()
    {
        $items = FaqItem::where('is_active', true)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        $categories = $items->map(function ($items, $category) {
            return [
                'name'  => $category,
                'items' => $items->map(fn ($item) => [
                    'id'       => $item->id,
                    'question' => $item->question,
                    'answer'   => $item->answer,
                ])->values(),
            ];
        })->values();

        return response()->json(['categories' => $categories]);
    }
}
