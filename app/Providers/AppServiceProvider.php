<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
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
        $this->configureScramble();
    }

    // ── Rate Limiters ─────────────────────────────────────────────────────────

    private function configureRateLimiters(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(120)->by('user:' . $request->user()->id)
                : Limit::perMinute(60)->by('ip:' . $request->ip());
        });

        RateLimiter::for('api-login', function (Request $request) {
            return Limit::perMinute(10)->by('login:' . $request->ip());
        });

        RateLimiter::for('api-admin', function (Request $request) {
            return Limit::perMinute(300)->by('admin:' . ($request->user()?->id ?? $request->ip()));
        });
    }

    // ── Scramble API Docs ─────────────────────────────────────────────────────

    private function configureScramble(): void
    {
        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer')
                );
            });
    }
}
