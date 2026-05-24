<?php

namespace App\Modules\Product\Domain\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function findById(int $id): ?Category;
    public function findBySlug(string $slug): ?Category;
    /** Root categories (no parent) with active children eager-loaded. */
    public function tree(): Collection;
    public function allActive(): Collection;
    public function create(array $data): Category;
    public function update(Category $category, array $data): Category;
    public function delete(Category $category): void;
}
