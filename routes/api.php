<?php

use App\Http\Controllers\API\HealthController;
use App\Modules\Inventory\Presentation\API\Controllers\InventoryController;
use App\Modules\Analytics\Presentation\API\Controllers\AnalyticsController;
use App\Modules\Analytics\Presentation\API\Controllers\AppConfigController;
use App\Modules\Campaign\Presentation\API\Controllers\CampaignController;
use App\Modules\Campaign\Presentation\API\Controllers\CouponController;
use App\Modules\Notification\Presentation\API\Controllers\DeviceController;
use App\Modules\Notification\Presentation\API\Controllers\NotificationController;
use App\Modules\Order\Presentation\API\Controllers\PaymentController;
use App\Modules\Order\Presentation\API\Controllers\CartController;
use App\Modules\Order\Presentation\API\Controllers\OrderController;
use App\Modules\Product\Presentation\API\Controllers\BrandController;
use App\Modules\Product\Presentation\API\Controllers\CategoryController;
use App\Modules\Product\Presentation\API\Controllers\ProductController;
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

    Route::get('/health', [HealthController::class, 'check'])->name('api.health');

    // Auth (public) — login gets its own strict throttle
    Route::prefix('auth')->name('api.auth.')->group(function () {
        Route::post('/firebase-login', [AuthController::class, 'firebaseLogin'])
            ->middleware('throttle:api-login')
            ->name('firebase-login');
    });

    // App Config (public — mobile app config)
    Route::get('/config', [AppConfigController::class, 'index'])->name('api.config');

    // Campaigns (public read — active campaigns visible to all)
    Route::prefix('campaigns')->name('api.campaigns.')->group(function () {
        Route::get('/', [CampaignController::class, 'index'])->name('index');
    });

    // Products (public read)
    Route::prefix('products')->name('api.products.')->group(function () {
        Route::get('/search', [ProductController::class, 'search'])->name('search');
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
    });

    // Categories (public read)
    Route::prefix('categories')->name('api.categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/{category}/products', [CategoryController::class, 'products'])->name('products');
    });

    // Brands (public read)
    Route::prefix('brands')->name('api.brands.')->group(function () {
        Route::get('/', [BrandController::class, 'index'])->name('index');
        Route::get('/{brand}/products', [BrandController::class, 'products'])->name('products');
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

        // Products — admin write
        Route::middleware('role:admin')->prefix('products')->name('api.products.admin.')->group(function () {
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
        });

        // Inventory — public read (stock check)
        Route::prefix('inventory')->name('api.inventory.')->group(function () {
            Route::get('/warehouses', [InventoryController::class, 'warehouses'])->name('warehouses');
            Route::get('/stock/{product}', [InventoryController::class, 'stock'])->name('stock');

            // Admin-only operations
            Route::middleware('role:admin')->group(function () {
                Route::get('/movements', [InventoryController::class, 'movements'])->name('movements');
                Route::get('/low-stock', [InventoryController::class, 'lowStock'])->name('low-stock');
                Route::post('/receive', [InventoryController::class, 'receive'])->name('receive');
                Route::post('/dispatch', [InventoryController::class, 'dispatch'])->name('dispatch');
                Route::post('/adjust', [InventoryController::class, 'adjust'])->name('adjust');
                Route::post('/transfer', [InventoryController::class, 'transfer'])->name('transfer');
            });
        });

        // Cart
        Route::prefix('cart')->name('api.cart.')->group(function () {
            Route::get('/', [CartController::class, 'show'])->name('show');
            Route::post('/items', [CartController::class, 'addItem'])->name('items.add');
            Route::put('/items/{item}', [CartController::class, 'updateItem'])->name('items.update');
            Route::delete('/items/{item}', [CartController::class, 'removeItem'])->name('items.remove');
            Route::delete('/', [CartController::class, 'clear'])->name('clear');
        });

        // Orders — customer
        Route::prefix('orders')->name('api.orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::post('/', [OrderController::class, 'store'])->name('store');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
            Route::post('/{order}/pay', [PaymentController::class, 'initiate'])->name('pay');
        });

        // Payment callback (iyzico posts here after checkout)
        Route::post('/payment/callback', [PaymentController::class, 'callback'])
            ->withoutMiddleware('auth:sanctum')
            ->name('api.payment.callback');

        // Coupons — validate (authenticated users)
        Route::prefix('coupons')->name('api.coupons.')->group(function () {
            Route::post('/validate', [CouponController::class, 'validate'])->name('validate');
        });

        // Notifications
        Route::prefix('notifications')->name('api.notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
            Route::post('/mark-read', [NotificationController::class, 'markRead'])->name('mark-read');
            Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        });

        // Device (FCM token) management
        Route::prefix('devices')->name('api.devices.')->group(function () {
            Route::post('/', [DeviceController::class, 'register'])->name('register');
            Route::delete('/', [DeviceController::class, 'unregister'])->name('unregister');
        });

        // Analytics — admin-only (higher rate limit for dashboards)
        Route::middleware(['role:admin', 'throttle:api-admin'])->prefix('admin/analytics')->name('api.admin.analytics.')->group(function () {
            Route::get('/dashboard',      [AnalyticsController::class, 'dashboard'])->name('dashboard');
            Route::get('/revenue',        [AnalyticsController::class, 'revenue'])->name('revenue');
            Route::get('/top-products',   [AnalyticsController::class, 'topProducts'])->name('top-products');
            Route::get('/top-customers',  [AnalyticsController::class, 'topCustomers'])->name('top-customers');
            Route::get('/order-statuses', [AnalyticsController::class, 'orderStatuses'])->name('order-statuses');
        });

        // Orders — admin management
        Route::middleware('role:admin')->prefix('admin/orders')->name('api.admin.orders.')->group(function () {
            Route::get('/', [OrderController::class, 'adminIndex'])->name('index');
            Route::put('/{order}/status', [OrderController::class, 'updateStatus'])->name('status');
        });

    });

});
