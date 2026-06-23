<?php

namespace App\Modules\Favorite\Application\UseCases;

use App\Modules\Favorite\Domain\Contracts\FavoriteRepositoryInterface;

class RemoveFavoriteUseCase
{
    public function __construct(private readonly FavoriteRepositoryInterface $favorites) {}

    public function executeById(int $id, int $userId): bool
    {
        return $this->favorites->removeById($id, $userId);
    }

    public function executeByProduct(int $userId, int $productId): bool
    {
        return $this->favorites->removeByProduct($userId, $productId);
    }
}
