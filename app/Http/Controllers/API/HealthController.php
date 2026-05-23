<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function check(): JsonResponse
    {
        $dbStatus = 'disconnected';
        $dbVersion = null;

        try {
            $result = DB::select('SELECT version()');
            $dbStatus = 'connected';
            $dbVersion = $result[0]->version ?? null;
        } catch (\Exception $e) {
            $dbStatus = 'error: ' . $e->getMessage();
        }

        return response()->json([
            'status'    => 'ok',
            'app'       => config('app.name'),
            'version'   => app()->version(),
            'timestamp' => now()->toIso8601String(),
            'database'  => [
                'status'  => $dbStatus,
                'driver'  => config('database.default'),
                'version' => $dbVersion,
            ],
            'environment' => config('app.env'),
        ]);
    }
}
