<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Tag jobs for better visibility in the Horizon dashboard
        Horizon::night();
    }

    /**
     * Register the Horizon gate.
     *
     * Only active admin users can access the Horizon dashboard in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = null) {
            return $user
                && $user->role === 'admin'
                && (bool) $user->is_active;
        });
    }
}
