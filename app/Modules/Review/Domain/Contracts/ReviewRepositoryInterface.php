<?php

namespace App\Modules\Review\Domain\Contracts;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReviewRepositoryInterface
{
    public function create(array $data): Review;

    public function existsForUserOrderProduct(int $userId, int $orderId, int $productId): bool;

    public function paginateForProduct(int $productId, int $perPage, string $sort): LengthAwarePaginator;

    /**
     * Returns ['average' => float, 'total' => int, 'distribution' => array]
     */
    public function summaryForProduct(int $productId): array;

    public function reviewedProductIds(int $orderId, int $userId): array;
}
