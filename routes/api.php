<?php

use App\Http\Controllers\API\HealthController;
use App\Modules\User\Presentation\API\Controllers\AddressController;
use App\Modules\User\Presentation\API\Controllers\AuthController;
use App\Modules\User\Presentation\API\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Nisa Ticaret v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ── Public Routes ─────────────────────────────────────────────────────────

    // Health Check
    Route::get('/health', [HealthController::class, 'check'])->name('api.health');

    // ── Authentication (public) ───────────────────────────────────────────────
    Route::prefix('auth')->name('api.auth.')->group(function () {
        Route::post('/firebase-login', [AuthController::class, 'firebaseLogin'])
            ->name('firebase-login');
    });

    // ── Protected Routes (Sanctum) ────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::prefix('auth')->name('api.auth.')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('/me', [AuthController::class, 'me'])->name('me');
        });

        // Profile
        Route::prefix('profile')->name('api.profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'show'])->name('show');
            Route::put('/', [ProfileController::class, 'update'])->name('update');
            Route::post('/avatar', [ProfileController::class, 'uploadAvatar'])->name('avatar');
        });

        // Addresses
        Route::prefix('addresses')->name('api.addresses.')->group(function () {
            Route::get('/', [AddressController::class, 'index'])->name('index');
            Route::post('/', [AddressController::class, 'store'])->name('store');
            Route::put('/{address}', [AddressController::class, 'update'])->name('update');
            Route::delete('/{address}', [AddressController::class, 'destroy'])->name('destroy');
            Route::post('/{address}/set-default', [AddressController::class, 'setDefault'])->name('set-default');
        });

    });

});
