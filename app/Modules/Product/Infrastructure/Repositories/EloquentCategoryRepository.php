<?php

namespace App\Modules\Product\Infrastructure\Repositories;

use App\Models\Category;
use App\Modules\Product\Domain\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function findById(int $id): ?Category
    {
        return Category::find($id);
    }

    public function findBySlug(string $slug): ?Category
    {
        return Category::where('slug', $slug)->first();
    }

    public function tree(): Collection
    {
        return Category::active()
            ->root()
            ->ordered()
            ->with(['childrenRecursive' => fn ($q) => $q->active()->ordered()])
            ->withCount('products')
            ->get();
    }

    public function allActive(): Collection
    {
        return Category::active()->ordered()->get();
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->fresh();
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }
}
