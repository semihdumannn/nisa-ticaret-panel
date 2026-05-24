<?php

namespace App\Modules\Inventory\Infrastructure\Repositories;

use App\Models\Warehouse;
use App\Modules\Inventory\Domain\Contracts\WarehouseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentWarehouseRepository implements WarehouseRepositoryInterface
{
    public function findById(int $id): ?Warehouse
    {
        return Warehouse::find($id);
    }

    public function findByCode(string $code): ?Warehouse
    {
        return Warehouse::where('code', $code)->first();
    }

    public function allActive(): Collection
    {
        return Warehouse::active()->orderBy('name')->get();
    }

    public function all(): Collection
    {
        return Warehouse::orderBy('name')->get();
    }

    public function create(array $data): Warehouse
    {
        return Warehouse::create($data);
    }

    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        $warehouse->update($data);
        return $warehouse->fresh();
    }
}
