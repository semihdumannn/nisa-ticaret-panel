<?php

namespace App\Providers;

use App\Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;
use App\Modules\Inventory\Domain\Contracts\StockMovementRepositoryInterface;
use App\Modules\Inventory\Domain\Contracts\WarehouseRepositoryInterface;
use App\Modules\Inventory\Infrastructure\Repositories\EloquentInventoryRepository;
use App\Modules\Inventory\Infrastructure\Repositories\EloquentStockMovementRepository;
use App\Modules\Inventory\Infrastructure\Repositories\EloquentWarehouseRepository;
use Illuminate\Support\ServiceProvider;

class InventoryModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(WarehouseRepositoryInterface::class,     EloquentWarehouseRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class,     EloquentInventoryRepository::class);
        $this->app->bind(StockMovementRepositoryInterface::class, EloquentStockMovementRepository::class);
    }

    public function boot(): void {}
}
