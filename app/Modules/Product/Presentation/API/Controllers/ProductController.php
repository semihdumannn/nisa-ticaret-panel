<?php

namespace App\Modules\Product\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Modules\Product\Application\DTOs\CreateProductDTO;
use App\Modules\Product\Application\UseCases\CreateProductUseCase;
use App\Modules\Product\Application\UseCases\UpdateProductUseCase;
use App\Modules\Product\Domain\Contracts\ProductRepositoryInterface;
use App\Modules\Product\Presentation\API\Requests\StoreProductRequest;
use App\Modules\Product\Presentation\API\Requests\UpdateProductRequest;
use App\Modules\Product\Presentation\API\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(private readonly ProductRepositoryInterface $products) {}

    /**
     * GET /api/v1/products
     * Public. Supports: ?category_id, ?brand_id, ?min_price, ?max_price, ?is_featured, ?sort, ?direction, ?per_page
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['category_id', 'brand_id', 'min_price', 'max_price', 'is_featured']);

        $paginated = $this->products->paginate(
            perPage:   (int) $request->get('per_page', 15),
            filters:   $filters,
            sort:      $request->get('sort', 'created_at'),
            direction: $request->get('direction', 'desc'),
        );

        return ProductResource::collection($paginated);
    }

    /**
     * GET /api/v1/products/{product}
     * Public.
     */
    public function show(Product $product): JsonResponse
    {
        $product->load(['brand', 'categories', 'images', 'variants']);

        return response()->json(['product' => new ProductResource($product)]);
    }

    /**
     * GET /api/v1/products/search
     * Public.
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);

        $results = Product::search($request->q)
            ->query(fn ($q) => $q->active()->with(['brand', 'images']))
            ->paginate(15);

        return ProductResource::collection($results);
    }

    /**
     * POST /api/v1/products
     * Admin only.
     */
    public function store(StoreProductRequest $request, CreateProductUseCase $createProduct): JsonResponse
    {
        $v       = $request->validated();
        $product = $createProduct->execute(new CreateProductDTO(
            name:        $v['name'],
            price:       (float) $v['price'],
            brandId:     $v['brand_id'] ?? null,
            sku:         $v['sku'] ?? null,
            description: $v['description'] ?? null,
            barcode:     $v['barcode'] ?? null,
            unit:        $v['unit'] ?? 'piece',
            costPrice:   isset($v['cost_price']) ? (float) $v['cost_price'] : null,
            taxRate:     (float) ($v['tax_rate'] ?? 20),
            minOrderQty: (int) ($v['min_order_qty'] ?? 1),
            maxOrderQty: isset($v['max_order_qty']) ? (int) $v['max_order_qty'] : null,
            isFeatured:  (bool) ($v['is_featured'] ?? false),
            isActive:    (bool) ($v['is_active'] ?? true),
            metadata:    $v['metadata'] ?? null,
            categoryIds: $v['category_ids'] ?? [],
        ));

        return response()->json([
            'message' => 'Product created successfully.',
            'product' => new ProductResource($product),
        ], 201);
    }

    /**
     * PUT /api/v1/products/{product}
     * Admin only.
     */
    public function update(UpdateProductRequest $request, Product $product, UpdateProductUseCase $updateProduct): JsonResponse
    {
        $product = $updateProduct->execute($product, $request->validated());

        return response()->json([
            'message' => 'Product updated successfully.',
            'product' => new ProductResource($product),
        ]);
    }

    /**
     * DELETE /api/v1/products/{product}
     * Admin only.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }
}
