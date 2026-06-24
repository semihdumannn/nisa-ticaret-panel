# Task 5 Report: Subscription Cron Job (Auto-Order)

## Status: COMPLETE

All files were already implemented and committed at `41f5c17`.

---

## Files Created/Modified

| File | Action |
|------|--------|
| `app/Console/Commands/ProcessSubscriptionOrdersCommand.php` | Created |
| `app/Modules/Subscription/Application/UseCases/ProcessDueSubscriptionsUseCase.php` | Created |
| `app/Modules/Subscription/Infrastructure/Repositories/EloquentSubscriptionRepository.php` | Modified (whereDate fix for SQLite compat) |
| `routes/console.php` | Modified (added schedule entry) |
| `tests/Feature/SubscriptionCronTest.php` | Created |

---

## Exact Field Names Found

- **Order FK for customer**: `customer_id` (from `Order::$fillable`)
- **OrderItem variant FK**: `variant_id` (from `OrderItem::$fillable`, relationship `belongsTo(ProductVariant, 'variant_id')`)
- **ProductVariant stock field**: `stock` (integer, cast in `ProductVariant::$casts`)
- **Initial order status used**: `OrderStatus::PENDING->value` = `'pending'`
- **Order `order_number`**: Required (not nullable) — set to `'TEMP'` then updated to `'SUB-YYYYMMDD-NNNNN'` pattern after insert
- **Order additional required fields**: `payment_status` (set to `'pending'`)

---

## FCM Approach

No `app/Notifications/` directory exists. The project uses a dedicated Notification module with a queued job:

```
app/Modules/Notification/Infrastructure/Jobs/SendPushNotificationJob.php
```

The use case dispatches `SendPushNotificationJob(userId, title, body, data)` for both:
1. **Stock insufficient**: title='Stok yetersiz', body='Stok yetersiz — abonelik siparişi oluşturulamadı'
2. **Order created**: title='Abonelik siparişi oluşturuldu', body='Abonelik siparişiniz oluşturuldu'

This matches the existing pattern used in `OrderNotificationListener` and `AdminOrderNotificationListener`.

---

## Algorithm Summary

1. `findDueToday()` fetches active subscriptions with `next_order_date <= today` (using `whereDate()` for SQLite compatibility)
2. Each subscription wrapped in try/catch — failures logged, processing continues
3. Stock check: `variant->stock < quantity` → dispatch FCM (stok yetersiz), log warning, increment `$skippedCount`, `continue`
4. Price: `$unitPrice = $variant->effectivePrice()`, `$discountedTotal = round($unitPrice * (1 - discount_rate/100) * quantity, 2)`
5. Create `Order` directly (bypasses `CreateOrderUseCase` min-order validation)
6. Create `OrderItem` via `$order->items()->create([...])`
7. Update subscription: `last_order_id`, `next_order_date` via `advanceDate()`
8. Dispatch FCM (order created)
9. Return `['processed' => N, 'skipped' => M]`

### advanceDate
- `weekly` → `+7 days`
- `biweekly` → `+14 days`
- `monthly` → `addMonthNoOverflow()`

---

## Test Results

### Targeted filter:
```
php artisan test --filter SubscriptionCronTest
```
**Result: 4/4 passed, 10 assertions, 379ms**

### Full suite:
```
php artisan test
```
**Result: 358/358 passed, 866 assertions, 7937ms**

Matches expected baseline 354 + 4 new cron tests = 358.

---

## Self-Review

- [x] Does NOT use `CreateOrderUseCase` — orders created directly with Eloquent
- [x] Each subscription wrapped in try/catch, failures don't abort others
- [x] `advanceDate` uses `addMonthNoOverflow()` for monthly plans
- [x] `effectivePrice()` used (not a non-existent `price` column on variant)
- [x] `variant_id` used in OrderItem (not `product_variant_id`)
- [x] `customer_id` used in Order (not `user_id`)
- [x] Scheduling registered in `routes/console.php` `dailyAt('08:00')`
- [x] Command auto-discovered (no explicit registration needed; `app/Console/Commands/` is autodiscovered)
- [x] `order_number` required field handled with TEMP → real pattern update
- [x] `payment_status` field included (required by DB, not in brief but needed)
- [x] `whereDate()` fix in repository ensures correct date comparison in SQLite tests

---

## Concerns / Notes

None — all tests pass cleanly. The `order_number` field was not mentioned in the brief but the `orders` table requires it (not nullable). The implementation generates `SUB-YYYYMMDD-NNNNN` format which is consistent with the rest of the codebase.

---

## Fix Report

### Fix 1: `pause_until` filter in `findDueToday()`
**File**: `app/Modules/Subscription/Infrastructure/Repositories/EloquentSubscriptionRepository.php`

Added filter to exclude subscriptions where `pause_until` is set to a future date:
```php
->where(function ($q) {
    $q->whereNull('pause_until')
      ->orWhereDate('pause_until', '<', now()->toDateString());
})
```

### Fix 2: DB transaction in `ProcessDueSubscriptionsUseCase`
**File**: `app/Modules/Subscription/Application/UseCases/ProcessDueSubscriptionsUseCase.php`

- Added `use Illuminate\Support\Facades\DB;` import
- Wrapped order creation, item creation, and subscription update in `DB::transaction()`
- Stock check remains outside transaction (read-only guard)
- Exception handling catches after transaction rolls back if any DB write fails

**Commit**: `1e992db44431c73eb71b0d76d19a613c100aae1d`
**Tests**: SubscriptionCronTest 4/4 passed; Full suite 358/358 passed
