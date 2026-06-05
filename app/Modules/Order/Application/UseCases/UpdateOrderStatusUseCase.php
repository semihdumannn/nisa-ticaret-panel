<?php

namespace App\Modules\Order\Application\UseCases;

use App\Models\Order;
use App\Modules\Notification\Domain\Events\OrderStatusUpdatedEvent;
use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use App\Modules\Order\Domain\Exceptions\InvalidOrderTransitionException;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Illuminate\Support\Facades\DB;

class UpdateOrderStatusUseCase
{
    public function __construct(private readonly OrderRepositoryInterface $orderRepo) {}

    /**
     * Transition an order to a new status (admin action).
     *
     * @throws InvalidOrderTransitionException
     */
    public function execute(Order $order, OrderStatus $newStatus, ?string $note = null, ?int $userId = null): Order
    {
        if (! $order->canTransitionTo($newStatus)) {
            throw new InvalidOrderTransitionException($order->orderStatus(), $newStatus);
        }

        $previousStatus = $order->status;

        $updated = DB::transaction(function () use ($order, $newStatus, $note, $userId) {
            $updates = ['status' => $newStatus->value];

            if ($newStatus === OrderStatus::DELIVERED) {
                $updates['delivered_at'] = now();
            }

            $updated = $this->orderRepo->update($order, $updates);
            $this->orderRepo->addHistory($updated, $newStatus->value, $note, $userId);

            return $updated;
        });

        // Event transaction dışında — Redis/queue hatası durum güncellemesini geri almaz
        try {
            event(new OrderStatusUpdatedEvent($updated, $previousStatus, $newStatus->value));
        } catch (\Throwable) {}

        return $updated;
    }
}
