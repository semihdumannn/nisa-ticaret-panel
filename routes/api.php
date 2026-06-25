<?php

use App\Http\Controllers\API\HealthController;
use App\Modules\Inventory\Presentation\API\Controllers\InventoryController;
use App\Modules\Analytics\Presentation\API\Controllers\AnalyticsController;
use App\Modules\Analytics\Presentation\API\Controllers\AppConfigController;
use App\Modules\Campaign\Presentation\API\Controllers\AdminCouponController;
use App\Modules\Campaign\Presentation\API\Controllers\CampaignController;
use App\Modules\Campaign\Presentation\API\Controllers\CouponController;
use App\Modules\Favorite\Presentation\API\Controllers\FavoriteController;
use App\Modules\Subscription\Presentation\API\Controllers\SubscriptionController;
use App\Modules\Notification\Presentation\API\Controllers\DeviceController;
use App\Modules\Notification\Presentation\API\Controllers\NotificationController;
use App\Modules\Review\Presentation\API\Controllers\ReviewController;
use App\Modules\Order\Presentation\API\Controllers\DeliveryController;
use App\Modules\Order\Presentation\API\Controllers\FieldAgentController;
use App\Modules\Order\Presentation\API\Controllers\PaymentController;
use App\Modules\Order\Presentation\API\Controllers\CartController;
use App\Modules\Order\Presentation\API\Controllers\OrderController;
use App\Modules\Product\Presentation\API\Controllers\ProductImageController;
use App\Modules\Product\Presentation\API\Controllers\BrandController;
use App\Modules\Product\Presentation\API\Controllers\CategoryController;
use App\Modules\Product\Presentation\API\Controllers\ProductController;
use App\Modules\User\Presentation\API\Controllers\AddressController;
use App\Modules\User\Presentation\API\Controllers\AdminUserController;
use App\Modules\User\Presentation\API\Controllers\AuthController;
use App\Modules\User\Presentation\API\Controllers\FieldAgentCustomerController;
use App\Modules\User\Presentation\API\Controllers\ProfileController;
use App\Modules\Faq\Presentation\API\Controllers\FaqController;
use App\Modules\Faq\Presentation\API\Controllers\AdminFaqController;
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
        Route::post('/device-register', [AuthController::class, 'deviceRegister'])
            ->middleware('throttle:api-login')
            ->name('device-register');
        Route::post('/totp-login', [AuthController::class, 'totpLogin'])
            ->middleware('throttle:api-login')
            ->name('totp-login');
        Route::get('/server-time', [AuthController::class, 'serverTime'])
            ->name('server-time');
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
        Route::get('/featured', [ProductController::class, 'featured'])->name('featured');
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::get('/{productId}/reviews', [ReviewController::class, 'productReviews'])->name('reviews');
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

    // FAQ (public read)
    Route::get('/help/faq', [FaqController::class, 'index'])->name('api.faq.index');

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
            Route::match(['PUT', 'PATCH'], '/', [ProfileController::class, 'update'])->name('update');
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

        // Product images — public read, admin write
        Route::prefix('products/{product}/images')->name('api.products.images.')->group(function () {
            Route::get('/', [ProductImageController::class, 'index'])->name('index');

            Route::middleware('role:admin')->group(function () {
                Route::post('/', [ProductImageController::class, 'store'])->name('store');
                Route::delete('/{image}', [ProductImageController::class, 'destroy'])->name('destroy');
                Route::put('/{image}/set-primary', [ProductImageController::class, 'setPrimary'])->name('set-primary');
            });
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

        // Coupons — list active + validate (authenticated users)
        Route::prefix('coupons')->name('api.coupons.')->group(function () {
            Route::get('/', [CouponController::class, 'index'])->name('index');
            Route::post('/validate', [CouponController::class, 'validate'])->name('validate');
        });

        // Notifications
        Route::prefix('notifications')->name('api.notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
            Route::post('/mark-read', [NotificationController::class, 'markRead'])->name('mark-read');
            Route::post('/{id}/mark-read', [NotificationController::class, 'markSingleRead'])->name('mark-read-single');
            Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        });

        // Device (FCM token) management
        Route::prefix('devices')->name('api.devices.')->group(function () {
            Route::post('/', [DeviceController::class, 'register'])->name('register');
            Route::delete('/', [DeviceController::class, 'unregister'])->name('unregister');
        });

        // Reviews
        Route::post('/reviews', [ReviewController::class, 'store'])->name('api.reviews.store');
        Route::get('/orders/{orderId}/review-status', [ReviewController::class, 'reviewStatus'])->name('api.reviews.status');

        // Subscriptions
        Route::prefix('subscriptions')->name('api.subscriptions.')->group(function () {
            Route::get('/',        [SubscriptionController::class, 'index'])->name('index');
            Route::post('/',       [SubscriptionController::class, 'store'])->name('store');
            Route::put('/{id}',    [SubscriptionController::class, 'update'])->name('update');
            Route::delete('/{id}', [SubscriptionController::class, 'destroy'])->name('destroy');
        });

        // Favorites
        Route::prefix('favorites')->name('api.favorites.')->group(function () {
            Route::get('/',                           [FavoriteController::class, 'index'])->name('index');
            Route::post('/',                          [FavoriteController::class, 'store'])->name('store');
            Route::delete('/by-product/{productId}',  [FavoriteController::class, 'destroyByProduct'])->name('destroy-by-product');
            Route::delete('/{id}',                    [FavoriteController::class, 'destroy'])->name('destroy');
        });

        // Analytics — admin-only (higher rate limit for dashboards)
        Route::middleware(['role:admin', 'throttle:api-admin'])->prefix('admin/analytics')->name('api.admin.analytics.')->group(function () {
            Route::get('/dashboard',      [AnalyticsController::class, 'dashboard'])->name('dashboard');
            Route::get('/revenue',        [AnalyticsController::class, 'revenue'])->name('revenue');
            Route::get('/top-products',   [AnalyticsController::class, 'topProducts'])->name('top-products');
            Route::get('/top-customers',  [AnalyticsController::class, 'topCustomers'])->name('top-customers');
            Route::get('/order-statuses',   [AnalyticsController::class, 'orderStatuses'])->name('order-statuses');
            Route::get('/customer-growth',  [AnalyticsController::class, 'customerGrowth'])->name('customer-growth');
        });

        // Orders — admin management
        Route::middleware('role:admin')->prefix('admin/orders')->name('api.admin.orders.')->group(function () {
            Route::get('/', [OrderController::class, 'adminIndex'])->name('index');
            Route::get('/{order}', [OrderController::class, 'adminShow'])->name('show');
            Route::put('/{order}/status', [OrderController::class, 'updateStatus'])->name('status');
            Route::put('/{order}/assign-delivery', [OrderController::class, 'assignDelivery'])->name('assign-delivery');
            Route::put('/{order}/assign-agent', [OrderController::class, 'assignAgent'])->name('assign-agent');
            Route::post('/{order}/notes', [OrderController::class, 'addNote'])->name('notes.store');
        });

        // Admin — users
        Route::middleware('role:admin')->prefix('admin/users')->name('api.admin.users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::put('/{id}/status', [AdminUserController::class, 'updateStatus'])->name('status');
            Route::put('/{id}/role', [AdminUserController::class, 'updateRole'])->name('role');
            Route::post('/{id}/toggle-block', [AdminUserController::class, 'toggleBlock'])->name('toggle-block');
            Route::get('/{id}/orders', [AdminUserController::class, 'orders'])->name('orders');
        });

        // Admin — coupons
        Route::middleware('role:admin')->prefix('admin/coupons')->name('api.admin.coupons.')->group(function () {
            Route::get('/', [AdminCouponController::class, 'index'])->name('index');
            Route::post('/', [AdminCouponController::class, 'store'])->name('store');
            Route::put('/{id}', [AdminCouponController::class, 'update'])->name('update');
            Route::delete('/{id}', [AdminCouponController::class, 'destroy'])->name('destroy');
        });

        // Admin — product toggle-active
        Route::middleware('role:admin')->prefix('admin/products')->name('api.admin.products.')->group(function () {
            Route::patch('/{product}/toggle-active', [ProductController::class, 'toggleActive'])->name('toggle-active');
        });

        // Admin — FAQ
        Route::middleware('role:admin')->prefix('admin/faq')->name('api.admin.faq.')->group(function () {
            Route::get('/', [AdminFaqController::class, 'index'])->name('index');
            Route::post('/', [AdminFaqController::class, 'store'])->name('store');
            Route::put('/{id}', [AdminFaqController::class, 'update'])->name('update');
            Route::delete('/{id}', [AdminFaqController::class, 'destroy'])->name('destroy');
        });

        // Field Agent routes
        Route::middleware('role:field_agent|admin')->prefix('field-agent')->name('api.field-agent.')->group(function () {
            Route::get('/stats', [FieldAgentController::class, 'stats'])->name('stats');
            Route::get('/today-orders', [FieldAgentController::class, 'todayOrders'])->name('today-orders');
            Route::post('/orders', [FieldAgentController::class, 'store'])->name('orders.store');

            Route::prefix('customers')->name('customers.')->group(function () {
                Route::get('/', [FieldAgentCustomerController::class, 'index'])->name('index');
                Route::post('/', [FieldAgentCustomerController::class, 'store'])->name('store');
                Route::get('/{id}', [FieldAgentCustomerController::class, 'show'])->name('show');
                Route::get('/{id}/orders', [FieldAgentCustomerController::class, 'orders'])->name('orders');
                Route::get('/{id}/addresses', [FieldAgentCustomerController::class, 'addresses'])->name('addresses');
                Route::post('/{id}/addresses', [FieldAgentCustomerController::class, 'addAddress'])->name('addresses.store');
            });
        });

        // Delivery & Field Agent routes
        Route::middleware('role:delivery|field_agent')->prefix('delivery')->name('api.delivery.')->group(function () {
            Route::get('/orders', [DeliveryController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [DeliveryController::class, 'show'])->name('orders.show');
            Route::put('/orders/{order}/on-the-way', [DeliveryController::class, 'markOnTheWay'])->name('orders.on-the-way');
            Route::put('/orders/{order}/deliver', [DeliveryController::class, 'markDelivered'])->name('orders.deliver');

            // Field agent only: assign orders to delivery staff
            Route::middleware('role:field_agent')->group(function () {
                Route::put('/orders/{order}/assign', [DeliveryController::class, 'assign'])->name('orders.assign');
            });
        });

    });

});
