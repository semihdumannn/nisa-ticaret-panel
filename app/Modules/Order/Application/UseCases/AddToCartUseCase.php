<?php

namespace App\Modules\Order\Application\UseCases;

use App\Models\Cart;
use App\Models\CartItem;
use App\Modules\Order\Application\DTOs\AddCartItemDTO;
use App\Modules\Order\Domain\Contracts\CartRepositoryInterface;

class AddToCartUseCase
{
    public function __construct(private readonly CartRepositoryInterface $cartRepo) {}

    public function execute(Cart $cart, AddCartItemDTO $dto): CartItem
    {
        return $this->cartRepo->addItem(
            $cart,
            $dto->productId,
            $dto->variantId,
            $dto->quantity,
        );
    }
}
