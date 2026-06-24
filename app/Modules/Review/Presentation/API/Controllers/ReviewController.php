<?php

namespace App\Modules\Review\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Review\Application\UseCases\GetOrderReviewStatusUseCase;
use App\Modules\Review\Application\UseCases\GetProductReviewsUseCase;
use App\Modules\Review\Application\UseCases\SubmitReviewUseCase;
use App\Modules\Review\Domain\Exceptions\ReviewNotAllowedException;
use App\Modules\Review\Presentation\API\Requests\SubmitReviewRequest;
use App\Modules\Review\Presentation\API\Resources\ReviewResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        private readonly SubmitReviewUseCase $submitReview,
        private readonly GetProductReviewsUseCase $getProductReviews,
        private readonly GetOrderReviewStatusUseCase $getOrderReviewStatus,
    ) {}

    /**
     * POST /api/v1/reviews
     */
    public function store(SubmitReviewRequest $request): JsonResponse
    {
        try {
            $review = $this->submitReview->execute(
                userId:    $request->user()->id,
                orderId:   (int) $request->order_id,
                productId: (int) $request->product_id,
                rating:    (int) $request->rating,
                comment:   $request->comment,
                tags:      $request->tags,
            );
        } catch (ReviewNotAllowedException $e) {
            return response()->json(['error' => $e->errorCode], 422);
        }

        return response()->json(new ReviewResource($review), 201);
    }

    /**
     * GET /api/v1/products/{productId}/reviews
     */
    public function productReviews(Request $request, int $productId): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $sort    = in_array($request->get('sort'), ['newest', 'highest', 'lowest'])
            ? $request->get('sort')
            : 'newest';

        $result    = $this->getProductReviews->execute($productId, $perPage, $sort);
        $paginator = $result['paginator'];
        $summary   = $result['summary'];

        $data = $paginator->getCollection()->map(fn ($review) => ReviewResource::listItem($review))->values();

        return response()->json([
            'data'    => $data,
            'summary' => [
                'average_rating' => $summary['average'],
                'total_reviews'  => $summary['total'],
                'distribution'   => $summary['distribution'],
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/orders/{orderId}/review-status
     */
    public function reviewStatus(Request $request, int $orderId): JsonResponse
    {
        $result = $this->getOrderReviewStatus->execute($orderId, $request->user()->id);

        return response()->json($result);
    }
}
