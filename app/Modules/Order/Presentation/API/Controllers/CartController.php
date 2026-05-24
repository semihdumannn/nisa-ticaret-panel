<?php

namespace App\Modules\Order\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Modules\Order\Application\DTOs\AddCartItemDTO;
use App\Modules\Order\Application\UseCases\AddToCartUseCase;
use App\Modules\Order\Application\UseCases\GetOrCreateCartUseCase;
use App\Modules\Order\Application\UseCases\RemoveFromCartUseCase;
use App\Modules\Order\Application\UseCases\UpdateCartItemUseCase;
use App\Modules\Order\Domain\Contracts\CartRepositoryInterface;
use App\Modules\Order\Presentation\API\Requests\AddCartItemRequest;
use App\Modules\Order\Presentation\API\Requests\UpdateCartItemRequest;
use App\Modules\Order\Presentation\API\Resources\CartResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartRepositoryInterface $cartRepo) {}

    /**
     * GET /api/v1/cart
     */
    public function show(Request $request, GetOrCreateCartUseCase $useCase): JsonResponse
    {
        $cart = $useCase->execute($request->user()->id);
        return response()->json(['data' => new CartResource($cart)]);
    }

    /**
     * POST /api/v1/cart/items
     */
    public function addItem(AddCartItemRequest $request, AddToCartUseCase $useCase, GetOrCreateCartUseCase $getCart): JsonResponse
    {
        $v    = $request->validated();
        $cart = $this->cartRepo->getOrCreate($request->user()->id);

        $useCase->execute($cart, new AddCartItemDTO(
            productId: $v['product_id'],
            quantity:  $v['quantity'],
            variantId: $v['variant_id'] ?? null,
        ));

        $cart->load('items.product', 'items.variant');

        return response()->json(['data' => new CartResource($cart)], 201);
    }

    /**
     * PUT /api/v1/cart/items/{item}
     */
    public function updateItem(UpdateCartItemRequest $request, CartItem $item, UpdateCartItemUseCase $useCase): JsonResponse
    {
        $this->authorizeCartItem($item, $request->user()->id);

        $qty = $request->validated()['quantity'];

        if ($qty === 0) {
            $useCase->execute($item, 0);
            $cart = $this->cartRepo->getOrCreate($request->user()->id);
            $cart->load('items.product', 'items.variant');
            return response()->json(['data' => new CartResource($cart)]);
        }

        $useCase->execute($item, $qty);

        $cart = $this->cartRepo->getOrCreate($request->user()->id);
        $cart->load('items.product', 'items.variant');

        return response()->json(['data' => new CartResource($cart)]);
    }

    /**
     * DELETE /api/v1/cart/items/{item}
     */
    public function removeItem(Request $request, CartItem $item, RemoveFromCartUseCase $useCase): JsonResponse
    {
        $this->authorizeCartItem($item, $request->user()->id);
        $useCase->execute($item);

        $cart = $this->cartRepo->getOrCreate($request->user()->id);
        $cart->load('items.product', 'items.variant');

        return response()->json(['data' => new CartResource($cart)]);
    }

    /**
     * DELETE /api/v1/cart
     */
    public function clear(Request $request): JsonResponse
    {
        $cart = $this->cartRepo->getOrCreate($request->user()->id);
        $this->cartRepo->clear($cart);

        $cart->load('items');

        return response()->json(['data' => new CartResource($cart)]);
    }

    private function authorizeCartItem(CartItem $item, int $userId): void
    {
        // Verify the item's owning cart belongs to the requesting user
        $ownsItem = \App\Models\Cart::where('id', $item->cart_id)
            ->where('user_id', $userId)
            ->exists();

        abort_unless($ownsItem, 403, 'This cart item does not belong to you.');
    }
}
