<?php

namespace App\Providers;

use App\Modules\User\Domain\Contracts\TotpServiceInterface;
use App\Modules\User\Domain\Contracts\UserRepositoryInterface;
use App\Modules\User\Infrastructure\External\Google2FaTotpService;
use App\Modules\User\Infrastructure\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;
use PragmaRX\Google2FA\Google2FA;

class UserModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repository
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

        // Bind TOTP service
        $this->app->bind(TotpServiceInterface::class, function ($app) {
            return new Google2FaTotpService($app->make(Google2FA::class));
        });
    }

    public function boot(): void
    {
        //
    }
}
