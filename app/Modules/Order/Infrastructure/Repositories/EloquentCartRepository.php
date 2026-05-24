<?php

namespace App\Modules\Order\Infrastructure\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Modules\Order\Domain\Contracts\CartRepositoryInterface;

class EloquentCartRepository implements CartRepositoryInterface
{
    public function getOrCreate(int $userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    public function addItem(Cart $cart, int $productId, ?int $variantId, int $quantity): CartItem
    {
        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        if ($item) {
            $item->increment('quantity', $quantity);
            return $item->fresh();
        }

        return CartItem::create([
            'cart_id'    => $cart->id,
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity'   => $quantity,
        ]);
    }

    public function updateItem(CartItem $item, int $quantity): CartItem
    {
        $item->update(['quantity' => $quantity]);
        return $item->fresh();
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
    }
}
