<?php

namespace App\Modules\Favorite\Application\UseCases;

use App\Modules\Favorite\Domain\Contracts\FavoriteRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListFavoritesUseCase
{
    public function __construct(private readonly FavoriteRepositoryInterface $favorites) {}

    public function execute(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->favorites->listForUser($userId, $perPage);
    }
}
