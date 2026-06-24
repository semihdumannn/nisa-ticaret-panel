<?php

namespace App\Modules\Review\Application\UseCases;

use App\Models\Order;
use App\Models\Review;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use App\Modules\Review\Domain\Contracts\ReviewRepositoryInterface;
use App\Modules\Review\Domain\Exceptions\ReviewNotAllowedException;

class SubmitReviewUseCase
{
    public function __construct(private readonly ReviewRepositoryInterface $reviews) {}

    public function execute(int $userId, int $orderId, int $productId, int $rating, ?string $comment, ?array $tags): Review
    {
        // 1. Load the order
        $order = Order::with('items')->findOrFail($orderId);

        // 2. Verify order belongs to the customer
        if ($order->customer_id !== $userId) {
            throw new ReviewNotAllowedException('NOT_YOUR_ORDER');
        }

        // 3. Verify order is delivered
        if ($order->status !== OrderStatus::DELIVERED->value) {
            throw new ReviewNotAllowedException('ORDER_NOT_DELIVERED');
        }

        // 4. Verify product is in the order
        $orderProductIds = $order->items->pluck('product_id')->all();
        if (! in_array($productId, $orderProductIds, true)) {
            throw new ReviewNotAllowedException('PRODUCT_NOT_IN_ORDER');
        }

        // 5. Verify not already reviewed
        if ($this->reviews->existsForUserOrderProduct($userId, $orderId, $productId)) {
            throw new ReviewNotAllowedException('ALREADY_REVIEWED');
        }

        // 6. Create the review
        $review = $this->reviews->create([
            'user_id'    => $userId,
            'order_id'   => $orderId,
            'product_id' => $productId,
            'rating'     => $rating,
            'comment'    => $comment,
            'tags'       => $tags,
        ]);

        // 7. Check if all order items are now reviewed → mark order as reviewed
        $reviewedIds = $this->reviews->reviewedProductIds($orderId, $userId);
        $allReviewed = empty(array_diff($orderProductIds, $reviewedIds));
        if ($allReviewed) {
            $order->is_reviewed = true;
            $order->save();
        }

        return $review;
    }
}
