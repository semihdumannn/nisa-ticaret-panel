# Task 4 Report: Subscriptions Module (CRUD Endpoints)

## Existing Model Field Names Found

### `app/Models/ProductVariant.php`
- No direct `price` column — has `price_adjustment` (decimal) only
- Effective price is calculated via `effectivePrice()` method: `product->price + price_adjustment`
- `is_active` (boolean) — confirmed
- `stock` (integer) — confirmed
- **Deviation from brief**: `SubscriptionResource` uses `$this->variant->effectivePrice()` instead of `$this->variant->price` (which doesn't exist)

### `app/Models/Address.php`
- `full_address` — confirmed (matches brief exactly)
- `title` — confirmed

## Factories Created

Two factories were missing and were created:

1. **`database/factories/ProductVariantFactory.php`**
   - `product_id`: `Product::factory()`
   - `sku`: unique `VAR-XXXXXXXX`
   - `name`: 2 fake words
   - `attributes`: `[]`
   - `price_adjustment`: `0.00`
   - `stock`: 0–100
   - `is_active`: `true`
   - States: `inactive()`, `outOfStock()`

2. **`database/factories/AddressFactory.php`**
   - `user_id`: `User::factory()`
   - `title`: random element (Home/Work/Office/Other)
   - `full_address`: `fake()->address()`
   - `district`, `city`: `fake()->city()`
   - `postal_code`: `fake()->postcode()`
   - `latitude`, `longitude`: `null`
   - `is_default`: `false`
   - States: `default()`

## Deviations from Brief

1. **`ProductVariant::price`**: The brief's `SubscriptionResource` shows `$this->variant->price`, but `ProductVariant` has no `price` column. Used `$this->variant->effectivePrice()` instead (calls `product->price + price_adjustment`). Also applies to `discounted_price` calculation.

2. **`SubscriptionException` 404 handling**: The brief says cancel/update should return 404 when subscription not found, but `SubscriptionException` is registered globally as 422. Fixed by catching `SUBSCRIPTION_NOT_FOUND` error code explicitly in the controller's `update` and `destroy` methods and returning 404.

3. **`discount_rate` JSON float serialization**: PHP's `json_encode(10.0)` produces `10` (integer), causing `assertJsonPath('discount_rate', 10.0)` to fail strict comparison. Fixed by passing `JSON_PRESERVE_ZERO_FRACTION` encoding option to the `store` response: `response()->json(..., 201, [], JSON_PRESERVE_ZERO_FRACTION)`.

4. **`UpdateSubscriptionRequest` `pause_until` validation**: Added `after_or_equal:today` in addition to `before_or_equal:today+90` for logical completeness.

## Files Created/Modified

### Created
- `database/migrations/2026_06_24_000004_create_subscriptions_table.php`
- `app/Models/Subscription.php`
- `app/Modules/Subscription/Domain/Contracts/SubscriptionRepositoryInterface.php`
- `app/Modules/Subscription/Domain/Exceptions/SubscriptionException.php`
- `app/Modules/Subscription/Application/UseCases/CreateSubscriptionUseCase.php`
- `app/Modules/Subscription/Application/UseCases/UpdateSubscriptionUseCase.php`
- `app/Modules/Subscription/Application/UseCases/CancelSubscriptionUseCase.php`
- `app/Modules/Subscription/Application/UseCases/ListSubscriptionsUseCase.php`
- `app/Modules/Subscription/Infrastructure/Repositories/EloquentSubscriptionRepository.php`
- `app/Modules/Subscription/Presentation/API/Controllers/SubscriptionController.php`
- `app/Modules/Subscription/Presentation/API/Requests/CreateSubscriptionRequest.php`
- `app/Modules/Subscription/Presentation/API/Requests/UpdateSubscriptionRequest.php`
- `app/Modules/Subscription/Presentation/API/Resources/SubscriptionResource.php`
- `app/Providers/SubscriptionModuleServiceProvider.php`
- `database/factories/ProductVariantFactory.php`
- `database/factories/AddressFactory.php`
- `tests/Feature/SubscriptionApiTest.php`

### Modified
- `bootstrap/providers.php` — added `SubscriptionModuleServiceProvider::class`
- `bootstrap/app.php` — added `SubscriptionException` renderer (returns `{"error": errorCode, "message": message}` with 422 status)
- `routes/api.php` — added subscriptions routes inside auth:sanctum group

## `findDueToday()` Implementation

Implemented in `EloquentSubscriptionRepository` for Task 5:
```php
public function findDueToday(): Collection
{
    return Subscription::where('status', 'active')
        ->where('next_order_date', '<=', now()->toDateString())
        ->with(['user', 'product', 'variant', 'address'])
        ->get();
}
```

## Test Results

```
php artisan test --filter SubscriptionApiTest
# 4 tests, 4 passed, 9 assertions — PASS

php artisan test
# 354 tests, 354 passed, 856 assertions — PASS
```

## Self-Review

- DDD structure follows `app/Modules/Favorite/` pattern exactly
- All business rules from brief implemented (variant check, address ownership, start_date, plan discounts, cancel sets next_order_date to today)
- `findDueToday()` ready for Task 5 cron job
- No admin subscription endpoint added (as instructed)
- Factories are reusable across future tests

## Fix Report

**Commit:** ea84fa7daf149250cd0379f3b0fdc2ce848e2b5d

**Issue:** `ProductVariant::effectivePrice()` accesses `$this->product->price`, causing one extra DB query per subscription when `SubscriptionResource` calls `discounted_price`. 

**Fix:** Updated `EloquentSubscriptionRepository` to eager-load `variant.product` in all three `with()` calls:
- `findByIdAndUser()` — changed `['product', 'variant', 'address']` to `['product', 'variant.product', 'address']`
- `listForUser()` — changed `['product', 'variant', 'address']` to `['product', 'variant.product', 'address']`
- `findDueToday()` — changed `['user', 'product', 'variant', 'address']` to `['user', 'product', 'variant.product', 'address']`

**Test Results:** All 354 tests passed (856 assertions); SubscriptionApiTest passed (4/4 tests, 9 assertions)
