<?php

namespace App\Modules\Inventory\Domain\Contracts;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;

interface WarehouseRepositoryInterface
{
    public function findById(int $id): ?Warehouse;
    public function findByCode(string $code): ?Warehouse;
    public function allActive(): Collection;
    public function all(): Collection;
    public function create(array $data): Warehouse;
    public function update(Warehouse $warehouse, array $data): Warehouse;
}
