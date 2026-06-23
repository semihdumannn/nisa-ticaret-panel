<?php

namespace App\Providers;

use App\Modules\Favorite\Domain\Contracts\FavoriteRepositoryInterface;
use App\Modules\Favorite\Infrastructure\Repositories\EloquentFavoriteRepository;
use Illuminate\Support\ServiceProvider;

class FavoriteModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FavoriteRepositoryInterface::class, EloquentFavoriteRepository::class);
    }

    public function boot(): void {}
}
