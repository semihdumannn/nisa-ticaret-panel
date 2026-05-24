<?php

namespace App\Modules\Inventory\Infrastructure\Repositories;

use App\Models\StockMovement;
use App\Modules\Inventory\Domain\Contracts\StockMovementRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentStockMovementRepository implements StockMovementRepositoryInterface
{
    public function record(array $data): StockMovement
    {
        return StockMovement::create($data);
    }

    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = StockMovement::with(['product', 'warehouse', 'user', 'variant'])
            ->latest();

        if (! empty($filters['product_id'])) {
            $query->forProduct($filters['product_id']);
        }

        if (! empty($filters['warehouse_id'])) {
            $query->forWarehouse($filters['warehouse_id']);
        }

        if (! empty($filters['type'])) {
            $query->ofType($filters['type']);
        }

        return $query->paginate($perPage);
    }
}
