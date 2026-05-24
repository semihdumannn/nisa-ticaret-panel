<?php

namespace App\Providers;

use App\Modules\Product\Domain\Contracts\BrandRepositoryInterface;
use App\Modules\Product\Domain\Contracts\CategoryRepositoryInterface;
use App\Modules\Product\Domain\Contracts\ProductRepositoryInterface;
use App\Modules\Product\Infrastructure\Repositories\EloquentBrandRepository;
use App\Modules\Product\Infrastructure\Repositories\EloquentCategoryRepository;
use App\Modules\Product\Infrastructure\Repositories\EloquentProductRepository;
use Illuminate\Support\ServiceProvider;

class ProductModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BrandRepositoryInterface::class, EloquentBrandRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
    }

    public function boot(): void {}
}
