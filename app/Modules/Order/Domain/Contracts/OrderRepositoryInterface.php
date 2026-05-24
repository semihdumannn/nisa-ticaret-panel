<?php

namespace App\Modules\Order\Domain\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;

    public function findByNumber(string $orderNumber): ?Order;

    /** Paginated list for a specific customer. */
    public function forCustomer(int $customerId, int $perPage = 15): LengthAwarePaginator;

    /** Admin: paginated all orders with optional filters. */
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator;

    public function create(array $data): Order;

    public function update(Order $order, array $data): Order;

    /** Append a history entry for the order. */
    public function addHistory(Order $order, string $status, ?string $note = null, ?int $userId = null): void;
}
