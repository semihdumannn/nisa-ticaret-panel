<?php

namespace App\Modules\Review\Application\UseCases;

use App\Models\Order;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use App\Modules\Review\Domain\Contracts\ReviewRepositoryInterface;

class GetOrderReviewStatusUseCase
{
    public function __construct(private readonly ReviewRepositoryInterface $reviews) {}

    public function execute(int $orderId, int $userId): array
    {
        $order = Order::with('items')->findOrFail($orderId);

        abort_if($order->customer_id !== $userId, 403);

        $canReview         = $order->status === OrderStatus::DELIVERED->value;
        $reviewedProductIds = $this->reviews->reviewedProductIds($orderId, $userId);
        $allProductIds     = $order->items->pluck('product_id')->all();
        $pendingProductIds = array_values(array_diff($allProductIds, $reviewedProductIds));

        return [
            'order_id'            => $order->id,
            'can_review'          => $canReview,
            'reviewed_product_ids' => $reviewedProductIds,
            'pending_product_ids'  => $pendingProductIds,
        ];
    }
}
