<?php

namespace App\Modules\Order\Application\UseCases;

use App\Models\CartItem;
use App\Modules\Order\Domain\Contracts\CartRepositoryInterface;

class RemoveFromCartUseCase
{
    public function __construct(private readonly CartRepositoryInterface $cartRepo) {}

    public function execute(CartItem $item): void
    {
        $this->cartRepo->removeItem($item);
    }
}
