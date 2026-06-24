<?php

namespace App\Providers;

use App\Modules\Subscription\Domain\Contracts\SubscriptionRepositoryInterface;
use App\Modules\Subscription\Infrastructure\Repositories\EloquentSubscriptionRepository;
use Illuminate\Support\ServiceProvider;

class SubscriptionModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SubscriptionRepositoryInterface::class, EloquentSubscriptionRepository::class);
    }

    public function boot(): void {}
}
