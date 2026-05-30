<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->forceHttpsIfNeeded();
        $this->configureRateLimiters();
        $this->configureScramble();
        $this->configurePgsqlPlanCache();
    }

    // ── HTTPS enforcement ─────────────────────────────────────────────────────
    // HF Spaces (and most proxies) terminate TLS externally; PHP-FPM sees
    // plain HTTP. Force the https scheme whenever APP_URL starts with https
    // so that asset(), url(), and route() all generate correct URLs.
    private function forceHttpsIfNeeded(): void
    {
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
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

    // ── PostgreSQL plan cache (Neon/PgBouncer) ───────────────────────────────
    // After schema migrations, PgBouncer backends may still have old cached
    // plans for statements like SELECT * FROM table. Forcing generic plans
    // prevents "cached plan must not change result type" errors.
    private function configurePgsqlPlanCache(): void
    {
        if (config('database.default') !== 'pgsql') {
            return;
        }

        Event::listen(ConnectionEstablished::class, function (ConnectionEstablished $event) {
            if ($event->connection->getDriverName() === 'pgsql') {
                try {
                    $event->connection->statement('SET plan_cache_mode = force_generic_plan');
                } catch (\Throwable) {
                    // Non-fatal: some PostgreSQL versions may not support this setting.
                }
            }
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
