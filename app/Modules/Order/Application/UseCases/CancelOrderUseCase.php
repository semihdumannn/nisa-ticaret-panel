<?php

namespace App\Modules\Order\Application\UseCases;

use App\Models\Inventory;
use App\Models\Order;
use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use App\Modules\Order\Domain\Exceptions\InvalidOrderTransitionException;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Illuminate\Support\Facades\DB;

class CancelOrderUseCase
{
    public function __construct(private readonly OrderRepositoryInterface $orderRepo) {}

    /**
     * Cancel an order and release all reserved stock.
     *
     * @throws InvalidOrderTransitionException
     */
    public function execute(Order $order, ?string $reason = null, ?int $userId = null): Order
    {
        if (! $order->canTransitionTo(OrderStatus::CANCELLED)) {
            throw new InvalidOrderTransitionException($order->orderStatus(), OrderStatus::CANCELLED);
        }

        return DB::transaction(function () use ($order, $reason, $userId) {
            // Release reserved stock for each item
            $order->load('items');

            foreach ($order->items as $item) {
                Inventory::where('product_id', $item->product_id)
                    ->where('variant_id', $item->variant_id)
                    ->orderByRaw('(quantity - reserved_quantity) ASC') // drain reservations from lowest-stock first
                    ->each(function (Inventory $inv) use ($item) {
                        $release = min($item->quantity, $inv->reserved_quantity);
                        if ($release > 0) {
                            $inv->decrement('reserved_quantity', $release);
                        }
                    });
            }

            $updated = $this->orderRepo->update($order, ['status' => OrderStatus::CANCELLED->value]);
            $this->orderRepo->addHistory(
                $updated,
                OrderStatus::CANCELLED->value,
                $reason ?? 'Order cancelled.',
                $userId,
            );

            return $updated;
        });
    }
}
