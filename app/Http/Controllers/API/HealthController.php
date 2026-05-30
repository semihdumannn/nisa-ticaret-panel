<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    public function check(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache'    => $this->checkCache(),
            'queue'    => $this->checkQueue(),
            'storage'  => $this->checkStorage(),
        ];

        $critical  = collect(['database', 'cache'])->every(fn ($k) => ($checks[$k]['status'] ?? '') === 'ok');
        $allOk     = collect($checks)->every(fn ($c) => $c['status'] === 'ok');

        return response()->json([
            'status'      => $allOk ? 'ok' : ($critical ? 'degraded' : 'error'),
            'app'         => config('app.name'),
            'version'     => app()->version(),
            'environment' => config('app.env'),
            'timestamp'   => now()->toIso8601String(),
            'checks'      => $checks,
        ], $critical ? 200 : 503);
    }

    // ── Individual checks ─────────────────────────────────────────────────────

    private function checkDatabase(): array
    {
        $driver = config('database.default');

        try {
            // Driver-specific version queries; fall back to SELECT 1 for unknown drivers
            $versionQuery = match ($driver) {
                'pgsql'  => 'SELECT version() AS version',
                'mysql', 'mariadb' => 'SELECT version() AS version',
                'sqlite' => 'SELECT sqlite_version() AS version',
                default  => null,
            };

            $version = null;
            if ($versionQuery) {
                $result  = DB::select($versionQuery);
                $version = $result[0]->version ?? null;
            } else {
                DB::select('SELECT 1');
            }

            return [
                'status'  => 'ok',
                'driver'  => $driver,
                'version' => $version,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'driver' => $driver,
                'error'  => config('app.debug') ? $e->getMessage() : 'Connection failed.',
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $key   = '__health_check__';
            $value = 'ok_' . now()->timestamp;

            Cache::put($key, $value, 10);
            $retrieved = Cache::get($key);
            Cache::forget($key);

            return [
                'status' => $retrieved === $value ? 'ok' : 'error',
                'driver' => config('cache.default'),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'driver' => config('cache.default'),
                'error'  => config('app.debug') ? $e->getMessage() : 'Cache unavailable.',
            ];
        }
    }

    private function checkQueue(): array
    {
        try {
            $size = Queue::size();

            return [
                'status'     => 'ok',
                'driver'     => config('queue.default'),
                'queue_size' => $size,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'driver' => config('queue.default'),
                'error'  => config('app.debug') ? $e->getMessage() : 'Queue unavailable.',
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $key = '__health_check__';
            Storage::put($key, 'ok');
            $exists = Storage::exists($key);
            Storage::delete($key);

            return [
                'status' => $exists ? 'ok' : 'error',
                'disk'   => config('filesystems.default'),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'disk'   => config('filesystems.default'),
                'error'  => config('app.debug') ? $e->getMessage() : 'Storage unavailable.',
            ];
        }
    }
}
