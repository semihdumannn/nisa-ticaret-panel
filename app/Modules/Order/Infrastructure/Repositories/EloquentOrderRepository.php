<?php

namespace App\Modules\Order\Infrastructure\Repositories;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    /** Relations always loaded on a single order (show / post-create). */
    private const FULL_RELATIONS = [
        'items.product',
        'items.variant',
        'address',
        'customer',
    ];

    /** Lighter set for paginated lists (avoids loading every address detail). */
    private const LIST_RELATIONS = [
        'items.product',
        'address',
        'customer',
    ];

    public function findById(int $id): ?Order
    {
        return Order::with(self::FULL_RELATIONS)->find($id);
    }

    public function findByNumber(string $orderNumber): ?Order
    {
        return Order::where('order_number', $orderNumber)->first();
    }

    /**
     * Customer-facing list: loads items + address (no admin data).
     */
    public function forCustomer(int $customerId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::forCustomer($customerId)
            ->with(['items.product', 'address'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Admin list: loads customer + items + address.
     */
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $q = Order::with(self::LIST_RELATIONS);

        if (!empty($filters['status'])) {
            $q->withStatus($filters['status']);
        }

        if (!empty($filters['customer_id'])) {
            $q->forCustomer((int) $filters['customer_id']);
        }

        return $q->latest()->paginate($perPage);
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);
        return $order->fresh();
    }

    public function addHistory(Order $order, string $status, ?string $note = null, ?int $userId = null): void
    {
        OrderStatusHistory::create([
            'order_id'   => $order->id,
            'status'     => $status,
            'note'       => $note,
            'created_by' => $userId,
        ]);
    }
}
