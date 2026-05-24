<?php

namespace App\Modules\Product\Domain\Contracts;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Collection;

interface BrandRepositoryInterface
{
    public function findById(int $id): ?Brand;
    public function findBySlug(string $slug): ?Brand;
    public function allActive(): Collection;
    public function create(array $data): Brand;
    public function update(Brand $brand, array $data): Brand;
    public function delete(Brand $brand): void;
}
