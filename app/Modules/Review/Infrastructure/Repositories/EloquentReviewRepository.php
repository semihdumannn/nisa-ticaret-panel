<?php

namespace App\Modules\Review\Infrastructure\Repositories;

use App\Models\Review;
use App\Modules\Review\Domain\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EloquentReviewRepository implements ReviewRepositoryInterface
{
    public function create(array $data): Review
    {
        return Review::create($data);
    }

    public function existsForUserOrderProduct(int $userId, int $orderId, int $productId): bool
    {
        return Review::where('user_id', $userId)
            ->where('order_id', $orderId)
            ->where('product_id', $productId)
            ->exists();
    }

    public function paginateForProduct(int $productId, int $perPage, string $sort): LengthAwarePaginator
    {
        $query = Review::with('user')
            ->where('product_id', $productId)
            ->where('is_approved', true);

        $query = match ($sort) {
            'highest' => $query->orderByDesc('rating')->orderByDesc('created_at'),
            'lowest'  => $query->orderBy('rating')->orderByDesc('created_at'),
            default   => $query->orderByDesc('created_at'), // newest
        };

        return $query->paginate($perPage);
    }

    public function summaryForProduct(int $productId): array
    {
        $rows = Review::where('product_id', $productId)
            ->where('is_approved', true)
            ->select('rating', DB::raw('count(*) as cnt'))
            ->groupBy('rating')
            ->pluck('cnt', 'rating');

        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[(string) $i] = (int) ($rows[$i] ?? 0);
        }

        $total   = array_sum($distribution);
        $average = $total > 0
            ? round(
                array_sum(array_map(fn ($rating, $cnt) => $rating * $cnt, array_keys($distribution), $distribution)) / $total,
                1
            )
            : 0.0;

        return [
            'average'      => $average,
            'total'        => $total,
            'distribution' => $distribution,
        ];
    }

    public function reviewedProductIds(int $orderId, int $userId): array
    {
        return Review::where('order_id', $orderId)
            ->where('user_id', $userId)
            ->pluck('product_id')
            ->toArray();
    }
}
