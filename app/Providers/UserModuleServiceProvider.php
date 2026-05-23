<?php

namespace App\Providers;

use App\Modules\User\Domain\Contracts\FirebaseAuthInterface;
use App\Modules\User\Domain\Contracts\UserRepositoryInterface;
use App\Modules\User\Infrastructure\External\FirebaseAuthService;
use App\Modules\User\Infrastructure\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Auth as FirebaseAuth;

class UserModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repository
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

        // Bind Firebase auth service
        $this->app->bind(FirebaseAuthInterface::class, function ($app) {
            return new FirebaseAuthService($app->make(FirebaseAuth::class));
        });
    }

    public function boot(): void
    {
        //
    }
}
