<?php

use App\Http\Controllers\API\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Nisa Ticaret
|--------------------------------------------------------------------------
|
| All API routes are prefixed with /api/v1
|
*/

Route::prefix('v1')->group(function () {

    // Health Check (public)
    Route::get('/health', [HealthController::class, 'check'])->name('api.health');

    // Auth routes (Phase 1)
    // Route::prefix('auth')->group(function () { ... });

    // Protected routes (Phase 1+)
    // Route::middleware('auth:sanctum')->group(function () { ... });

});
