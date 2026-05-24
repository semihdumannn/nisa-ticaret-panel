<?php

namespace App\Modules\Inventory\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Modules\Inventory\Application\DTOs\StockOperationDTO;
use App\Modules\Inventory\Application\DTOs\TransferStockDTO;
use App\Modules\Inventory\Application\UseCases\AdjustStockUseCase;
use App\Modules\Inventory\Application\UseCases\CheckLowStockUseCase;
use App\Modules\Inventory\Application\UseCases\DispatchStockUseCase;
use App\Modules\Inventory\Application\UseCases\ReceiveStockUseCase;
use App\Modules\Inventory\Application\UseCases\TransferStockUseCase;
use App\Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;
use App\Modules\Inventory\Domain\Contracts\StockMovementRepositoryInterface;
use App\Modules\Inventory\Domain\Contracts\WarehouseRepositoryInterface;
use App\Modules\Inventory\Domain\Exceptions\InsufficientStockException;
use App\Modules\Inventory\Presentation\API\Requests\AdjustStockRequest;
use App\Modules\Inventory\Presentation\API\Requests\StockOperationRequest;
use App\Modules\Inventory\Presentation\API\Requests\TransferStockRequest;
use App\Modules\Inventory\Presentation\API\Resources\InventoryResource;
use App\Modules\Inventory\Presentation\API\Resources\StockMovementResource;
use App\Modules\Inventory\Presentation\API\Resources\WarehouseResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InventoryController extends Controller
{
    public function __construct(
        private readonly WarehouseRepositoryInterface  $warehouses,
        private readonly InventoryRepositoryInterface  $inventory,
        private readonly StockMovementRepositoryInterface $movements,
    ) {}

    // ── Warehouses ────────────────────────────────────────────────────────────

    /**
     * GET /api/v1/inventory/warehouses
     */
    public function warehouses(): JsonResponse
    {
        return response()->json([
            'data' => WarehouseResource::collection($this->warehouses->allActive()),
        ]);
    }

    // ── Stock levels ──────────────────────────────────────────────────────────

    /**
     * GET /api/v1/inventory/stock/{product}
     * Stock levels for a product across all warehouses.
     */
    public function stock(Product $product): JsonResponse
    {
        $rows = $this->inventory->forProduct($product->id);

        return response()->json([
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'stock'        => InventoryResource::collection($rows),
        ]);
    }

    /**
     * GET /api/v1/inventory/low-stock
     */
    public function lowStock(Request $request, CheckLowStockUseCase $checker): JsonResponse
    {
        $threshold = (int) $request->get('threshold', 5);

        return response()->json([
            'threshold' => $threshold,
            'data'      => InventoryResource::collection($checker->lowStock($threshold)),
            'summary'   => $checker->summary($threshold),
        ]);
    }

    // ── Movements ─────────────────────────────────────────────────────────────

    /**
     * GET /api/v1/inventory/movements
     */
    public function movements(Request $request): AnonymousResourceCollection
    {
        $paginated = $this->movements->paginate(
            20,
            $request->only(['product_id', 'warehouse_id', 'type']),
        );

        return StockMovementResource::collection($paginated);
    }

    // ── Write operations (admin) ──────────────────────────────────────────────

    /**
     * POST /api/v1/inventory/receive
     */
    public function receive(StockOperationRequest $request, ReceiveStockUseCase $useCase): JsonResponse
    {
        $v         = $request->validated();
        $inventory = $useCase->execute(new StockOperationDTO(
            productId:   $v['product_id'],
            warehouseId: $v['warehouse_id'],
            quantity:    $v['quantity'],
            reason:      $v['reason'] ?? null,
            variantId:   $v['variant_id'] ?? null,
            userId:      $request->user()?->id,
        ));

        return response()->json([
            'message'   => "Received {$v['quantity']} units.",
            'inventory' => new InventoryResource($inventory->load('warehouse')),
        ]);
    }

    /**
     * POST /api/v1/inventory/dispatch
     */
    public function dispatch(StockOperationRequest $request, DispatchStockUseCase $useCase): JsonResponse
    {
        $v = $request->validated();

        try {
            $inventory = $useCase->execute(new StockOperationDTO(
                productId:   $v['product_id'],
                warehouseId: $v['warehouse_id'],
                quantity:    $v['quantity'],
                reason:      $v['reason'] ?? null,
                variantId:   $v['variant_id'] ?? null,
                userId:      $request->user()?->id,
            ));
        } catch (InsufficientStockException $e) {
            return response()->json([
                'message'   => $e->getMessage(),
                'requested' => $e->requested,
                'available' => $e->available,
            ], 422);
        }

        return response()->json([
            'message'   => "Dispatched {$v['quantity']} units.",
            'inventory' => new InventoryResource($inventory->load('warehouse')),
        ]);
    }

    /**
     * POST /api/v1/inventory/adjust
     */
    public function adjust(AdjustStockRequest $request, AdjustStockUseCase $useCase): JsonResponse
    {
        $v         = $request->validated();
        $inventory = $useCase->execute(new StockOperationDTO(
            productId:   $v['product_id'],
            warehouseId: $v['warehouse_id'],
            quantity:    $v['quantity'],
            reason:      $v['reason'],
            variantId:   $v['variant_id'] ?? null,
            userId:      $request->user()?->id,
        ));

        return response()->json([
            'message'   => "Stock adjusted to {$v['quantity']} units.",
            'inventory' => new InventoryResource($inventory->load('warehouse')),
        ]);
    }

    /**
     * POST /api/v1/inventory/transfer
     */
    public function transfer(TransferStockRequest $request, TransferStockUseCase $useCase): JsonResponse
    {
        $v = $request->validated();

        try {
            $useCase->execute(new TransferStockDTO(
                productId:       $v['product_id'],
                fromWarehouseId: $v['from_warehouse_id'],
                toWarehouseId:   $v['to_warehouse_id'],
                quantity:        $v['quantity'],
                reason:          $v['reason'] ?? null,
                variantId:       $v['variant_id'] ?? null,
                userId:          $request->user()?->id,
            ));
        } catch (InsufficientStockException $e) {
            return response()->json([
                'message'   => $e->getMessage(),
                'requested' => $e->requested,
                'available' => $e->available,
            ], 422);
        }

        return response()->json(['message' => "Transferred {$v['quantity']} units."]);
    }
}
