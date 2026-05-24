<?php

namespace App\Modules\Order\Infrastructure\Repositories;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function findById(int $id): ?Order
    {
        return Order::with(['items.product', 'items.variant', 'address', 'customer'])->find($id);
    }

    public function findByNumber(string $orderNumber): ?Order
    {
        return Order::where('order_number', $orderNumber)->first();
    }

    public function forCustomer(int $customerId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::forCustomer($customerId)
            ->with(['items'])
            ->latest()
            ->paginate($perPage);
    }

    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $q = Order::with(['customer', 'items']);

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
