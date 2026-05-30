<?php

namespace App\Modules\Product\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\Domain\Contracts\CategoryRepositoryInterface;
use App\Modules\Product\Domain\Contracts\ProductRepositoryInterface;
use App\Modules\Product\Presentation\API\Resources\CategoryResource;
use App\Modules\Product\Presentation\API\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
        private readonly ProductRepositoryInterface $products,
    ) {}

    /**
     * GET /api/v1/categories
     * Public. Returns tree with product counts. Cached 24 hours.
     */
    public function index(): JsonResponse
    {
        $data = Cache::remember('categories.tree', 86400, function () {
            $tree = $this->categories->tree();
            return CategoryResource::collection($tree)->resolve();
        });

        return response()->json(['data' => $data]);
    }

    /**
     * GET /api/v1/categories/{category}/products
     * Public.
     */
    public function products(Request $request, int $category): AnonymousResourceCollection
    {
        $paginated = $this->products->paginate(
            perPage: 15,
            filters: ['category_id' => $category],
        );

        return ProductResource::collection($paginated);
    }
}
