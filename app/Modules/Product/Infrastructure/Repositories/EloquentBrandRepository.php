<?php

namespace App\Modules\Product\Infrastructure\Repositories;

use App\Models\Brand;
use App\Modules\Product\Domain\Contracts\BrandRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentBrandRepository implements BrandRepositoryInterface
{
    public function findById(int $id): ?Brand
    {
        return Brand::find($id);
    }

    public function findBySlug(string $slug): ?Brand
    {
        return Brand::where('slug', $slug)->first();
    }

    public function allActive(): Collection
    {
        return Brand::active()->ordered()->withCount('products')->get();
    }

    public function create(array $data): Brand
    {
        return Brand::create($data);
    }

    public function update(Brand $brand, array $data): Brand
    {
        $brand->update($data);

        return $brand->fresh();
    }

    public function delete(Brand $brand): void
    {
        $brand->delete();
    }
}
