<?php

namespace App\Modules\Favorite\Application\UseCases;

use App\Models\Favorite;
use App\Modules\Favorite\Domain\Contracts\FavoriteRepositoryInterface;

class AddFavoriteUseCase
{
    public function __construct(private readonly FavoriteRepositoryInterface $favorites) {}

    public function execute(int $userId, int $productId): Favorite
    {
        return $this->favorites->add($userId, $productId);
    }
}
