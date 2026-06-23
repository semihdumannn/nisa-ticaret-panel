<?php

namespace App\Modules\Favorite\Domain\Contracts;

use App\Models\Favorite;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FavoriteRepositoryInterface
{
    public function listForUser(int $userId, int $perPage = 20): LengthAwarePaginator;

    public function findByUserAndProduct(int $userId, int $productId): ?Favorite;

    public function add(int $userId, int $productId): Favorite;

    /** Returns false if not found or not owned by the given user. */
    public function removeById(int $id, int $userId): bool;

    public function removeByProduct(int $userId, int $productId): bool;

    public function isProductFavoritedByUser(int $productId, int $userId): bool;

    /** Returns an array of product IDs from the given list that are favorited by the user. */
    public function getFavoritedProductIds(int $userId, array $productIds): array;
}
