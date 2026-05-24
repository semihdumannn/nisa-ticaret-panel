<?php

namespace App\Modules\Order\Application\UseCases;

use App\Models\Cart;
use App\Modules\Order\Domain\Contracts\CartRepositoryInterface;

class GetOrCreateCartUseCase
{
    public function __construct(private readonly CartRepositoryInterface $cartRepo) {}

    public function execute(int $userId): Cart
    {
        $cart = $this->cartRepo->getOrCreate($userId);
        return $cart->load('items.product', 'items.variant');
    }
}
