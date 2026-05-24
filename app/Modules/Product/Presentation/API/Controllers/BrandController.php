<?php

namespace App\Modules\Product\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\Domain\Contracts\BrandRepositoryInterface;
use App\Modules\Product\Domain\Contracts\ProductRepositoryInterface;
use App\Modules\Product\Presentation\API\Resources\BrandResource;
use App\Modules\Product\Presentation\API\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class BrandController extends Controller
{
    public function __construct(
        private readonly BrandRepositoryInterface $brands,
        private readonly ProductRepositoryInterface $products,
    ) {}

    /**
     * GET /api/v1/brands
     * Public. Cached 24 hours.
     */
    public function index(): JsonResponse
    {
        $brands = Cache::remember('brands.all', 86400, fn () => $this->brands->allActive());

        return response()->json(['data' => BrandResource::collection($brands)]);
    }

    /**
     * GET /api/v1/brands/{brand}/products
     * Public.
     */
    public function products(Request $request, int $brand): AnonymousResourceCollection
    {
        $paginated = $this->products->paginate(
            perPage: 15,
            filters: ['brand_id' => $brand],
        );

        return ProductResource::collection($paginated);
    }
}
