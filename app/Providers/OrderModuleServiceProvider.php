<?php

namespace App\Providers;

use App\Modules\Order\Domain\Contracts\CartRepositoryInterface;
use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use App\Modules\Order\Infrastructure\Repositories\EloquentCartRepository;
use App\Modules\Order\Infrastructure\Repositories\EloquentOrderRepository;
use Illuminate\Support\ServiceProvider;

class OrderModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CartRepositoryInterface::class,  EloquentCartRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
    }

    public function boot(): void {}
}
