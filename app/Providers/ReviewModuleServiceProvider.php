<?php

namespace App\Providers;

use App\Modules\Review\Domain\Contracts\ReviewRepositoryInterface;
use App\Modules\Review\Infrastructure\Repositories\EloquentReviewRepository;
use Illuminate\Support\ServiceProvider;

class ReviewModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ReviewRepositoryInterface::class, EloquentReviewRepository::class);
    }

    public function boot(): void {}
}
