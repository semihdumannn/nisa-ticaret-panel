<?php

namespace App\Modules\Review\Application\UseCases;

use App\Modules\Review\Domain\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetProductReviewsUseCase
{
    public function __construct(private readonly ReviewRepositoryInterface $reviews) {}

    public function execute(int $productId, int $perPage, string $sort): array
    {
        $paginator = $this->reviews->paginateForProduct($productId, $perPage, $sort);
        $summary   = $this->reviews->summaryForProduct($productId);

        return [
            'paginator' => $paginator,
            'summary'   => $summary,
        ];
    }
}
