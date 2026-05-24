<?php

namespace App\Modules\Order\Application\UseCases;

use App\Models\CartItem;
use App\Modules\Order\Domain\Contracts\CartRepositoryInterface;

class UpdateCartItemUseCase
{
    public function __construct(private readonly CartRepositoryInterface $cartRepo) {}

    public function execute(CartItem $item, int $quantity): CartItem
    {
        if ($quantity <= 0) {
            $this->cartRepo->removeItem($item);
            return $item; // soft return; caller should treat as removed
        }

        return $this->cartRepo->updateItem($item, $quantity);
    }
}
