<?php

namespace App\Modules\Favorite\Infrastructure\Repositories;

use App\Models\Favorite;
use App\Modules\Favorite\Domain\Contracts\FavoriteRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentFavoriteRepository implements FavoriteRepositoryInterface
{
    public function listForUser(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return Favorite::with('product')
            ->where('user_id', $userId)
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function findByUserAndProduct(int $userId, int $productId): ?Favorite
    {
        return Favorite::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
    }

    public function add(int $userId, int $productId): Favorite
    {
        return Favorite::create([
            'user_id'    => $userId,
            'product_id' => $productId,
        ]);
    }

    public function removeById(int $id, int $userId): bool
    {
        $favorite = Favorite::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($favorite === null) {
            return false;
        }

        $favorite->delete();

        return true;
    }

    public function removeByProduct(int $userId, int $productId): bool
    {
        $deleted = Favorite::where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();

        return $deleted > 0;
    }

    public function isProductFavoritedByUser(int $productId, int $userId): bool
    {
        return Favorite::where('product_id', $productId)
            ->where('user_id', $userId)
            ->exists();
    }

    public function getFavoritedProductIds(int $userId, array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        return Favorite::where('user_id', $userId)
            ->whereIn('product_id', $productIds)
            ->pluck('product_id')
            ->toArray();
    }
}
