<?php

namespace App\Modules\Faq\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FaqItem;
use App\Modules\Faq\Presentation\API\Requests\StoreFaqRequest;
use App\Modules\Faq\Presentation\API\Requests\UpdateFaqRequest;
use Illuminate\Http\JsonResponse;

class AdminFaqController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(FaqItem::orderBy('category')->orderBy('sort_order')->paginate(20));
    }

    public function store(StoreFaqRequest $request): JsonResponse
    {
        $item = FaqItem::create($request->validated());

        return response()->json($item, 201);
    }

    public function update(UpdateFaqRequest $request, $id): JsonResponse
    {
        $item = FaqItem::findOrFail($id);
        $item->update($request->validated());

        return response()->json($item);
    }

    public function destroy($id): JsonResponse
    {
        FaqItem::destroy($id);

        return response()->json(null, 204);
    }
}
