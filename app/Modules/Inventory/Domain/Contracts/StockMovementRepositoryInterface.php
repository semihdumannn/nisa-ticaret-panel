<?php

namespace App\Modules\Inventory\Domain\Contracts;

use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StockMovementRepositoryInterface
{
    public function record(array $data): StockMovement;

    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator;
}
