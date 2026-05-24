<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->configureRateLimiters();
    }

    // ── Rate Limiters ─────────────────────────────────────────────────────────

    private function configureRateLimiters(): void
    {
        /**
         * Default API limiter:
         *  - Authenticated users: 120 req/min keyed by user ID
         *  - Guests: 60 req/min keyed by IP
         */
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(120)->by('user:' . $request->user()->id)
                : Limit::perMinute(60)->by('ip:' . $request->ip());
        });

        /**
         * Strict limiter for auth endpoints (brute-force protection):
         * 10 login attempts per minute per IP.
         */
        RateLimiter::for('api-login', function (Request $request) {
            return Limit::perMinute(10)->by('login:' . $request->ip());
        });

        /**
         * Admin limiter: 300 req/min (dashboards & reports make many calls).
         */
        RateLimiter::for('api-admin', function (Request $request) {
            return Limit::perMinute(300)->by('admin:' . ($request->user()?->id ?? $request->ip()));
        });
    }
}
