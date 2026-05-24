<?php

namespace App\Modules\Order\Domain\Contracts;

use App\Models\Cart;
use App\Models\CartItem;

interface CartRepositoryInterface
{
    /** Find existing cart for user or create a fresh one. */
    public function getOrCreate(int $userId): Cart;

    /** Add or increment a product in the cart. Returns the updated CartItem. */
    public function addItem(Cart $cart, int $productId, ?int $variantId, int $quantity): CartItem;

    /** Set absolute quantity on an existing CartItem. */
    public function updateItem(CartItem $item, int $quantity): CartItem;

    /** Remove a specific CartItem. */
    public function removeItem(CartItem $item): void;

    /** Delete all items in the cart (keep the cart row). */
    public function clear(Cart $cart): void;
}
