<?php

namespace App\Modules\Faq\Presentation\API\Controllers;

use App\Models\FaqItem;
use App\Modules\Faq\Presentation\API\Requests\StoreFaqRequest;
use App\Modules\Faq\Presentation\API\Requests\UpdateFaqRequest;

class AdminFaqController
{
    public function index()
    {
        return FaqItem::orderBy('category')
            ->orderBy('sort_order')
            ->paginate(20);
    }

    public function store(StoreFaqRequest $request)
    {
        $item = FaqItem::create($request->validated());

        return response()->json($item, 201);
    }

    public function update(UpdateFaqRequest $request, $id)
    {
        $item = FaqItem::findOrFail($id);
        $item->update($request->validated());

        return response()->json($item);
    }

    public function destroy($id)
    {
        FaqItem::destroy($id);

        return response()->noContent();
    }
}
