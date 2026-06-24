# Task 5: Subscription Cron Job (Auto-Order)

## Context

The Subscriptions module was created in Task 4. This task builds on top of it: a scheduled command that runs daily at 08:00 and auto-creates orders for due subscriptions.

**Prerequisite:** Task 4 must be complete (subscriptions table, Subscription model, SubscriptionRepositoryInterface with `findDueToday()`, SubscriptionModuleServiceProvider).

**Important:** Do NOT recreate anything from Task 4. Only create the new files listed below.

## Files to Create/Modify

| Action | Path |
|--------|------|
| Create | `app/Console/Commands/ProcessSubscriptionOrdersCommand.php` |
| Create | `app/Modules/Subscription/Application/UseCases/ProcessDueSubscriptionsUseCase.php` |
| Modify | `routes/console.php` (or `bootstrap/app.php` schedule block if `console.php` doesn't exist) |
| Create | `tests/Feature/SubscriptionCronTest.php` |

## The Cron Logic

**Trigger:** `php artisan subscriptions:process-orders`

**Schedule:** Daily at 08:00 via `Schedule::command('subscriptions:process-orders')->dailyAt('08:00')`

### Algorithm

```
subscriptions WHERE status = 'active'
  AND next_order_date <= today
  AND (pause_until IS NULL OR pause_until < today)
```

For each subscription found:
1. Load related product variant — check `variant->stock >= subscription->quantity`
   - If insufficient stock: send FCM notification "Stok yetersiz — abonelik siparişi oluşturulamadı", log warning, skip to next
2. Calculate discounted total: `variant->price * quantity * (1 - discount_rate / 100)`
3. Create an order record directly (use the Order model, not CreateOrderUseCase, since that has minimum order amount validation which should NOT apply to subscriptions):
   ```php
   $order = Order::create([
       'customer_id'    => $subscription->user_id,
       'address_id'     => $subscription->address_id,
       'status'         => 'confirmed', // or whatever initial status
       'total'          => $discountedTotal,
       'subtotal'       => $discountedTotal,
       'payment_method' => 'subscription',
       'notes'          => "Abonelik #{$subscription->id} — otomatik sipariş",
   ]);
   $order->items()->create([
       'product_id'   => $subscription->product_id,
       'variant_id'   => $subscription->variant_id,
       'product_name' => $subscription->product->name,
       'quantity'     => $subscription->quantity,
       'unit_price'   => (float) $subscription->variant->price,
       'tax_rate'     => 0,
       'total'        => $discountedTotal,
   ]);
   ```
4. Update subscription:
   ```php
   $subscription->update([
       'last_order_id'   => $order->id,
       'next_order_date' => $this->advanceDate($subscription->plan, $subscription->next_order_date),
   ]);
   ```
5. Send FCM notification: "Abonelik siparişiniz oluşturuldu" — use the existing notification dispatch pattern. Check `app/Notifications/` or how existing notifications are dispatched (look at `NotificationController` or `OrderController` for the pattern). If FCM notifications use jobs, dispatch a job. If they use `Notification::send()`, use that. Match whatever pattern already exists.

### advanceDate logic

```php
private function advanceDate(string $plan, \Carbon\Carbon $currentDate): \Carbon\Carbon
{
    return match($plan) {
        'weekly'   => $currentDate->addDays(7),
        'biweekly' => $currentDate->addDays(14),
        'monthly'  => $currentDate->addMonthNoOverflow(),
    };
}
```

Note: `addMonthNoOverflow()` ensures Jan 31 + 1 month = Feb 28, not Mar 3.

## `app/Console/Commands/ProcessSubscriptionOrdersCommand.php`

```php
<?php
namespace App\Console\Commands;

use App\Modules\Subscription\Application\UseCases\ProcessDueSubscriptionsUseCase;
use Illuminate\Console\Command;

class ProcessSubscriptionOrdersCommand extends Command
{
    protected $signature   = 'subscriptions:process-orders';
    protected $description = 'Create orders for subscriptions due today';

    public function handle(ProcessDueSubscriptionsUseCase $useCase): int
    {
        $result = $useCase->execute();
        $this->info("Processed: {$result['processed']} subscriptions, {$result['skipped']} skipped (stock).");
        return Command::SUCCESS;
    }
}
```

## `ProcessDueSubscriptionsUseCase`

Return value:
```php
return ['processed' => $processedCount, 'skipped' => $skippedCount];
```

Wrap each subscription in a try/catch. Log exceptions to the error log but do NOT re-throw — processing one failed subscription must not stop the others.

## Scheduling

**Check if `routes/console.php` exists.** If it does, add:
```php
Schedule::command('subscriptions:process-orders')->dailyAt('08:00');
```

If not, add to the schedule callback in `bootstrap/app.php`:
```php
->withSchedule(function (Schedule $schedule) {
    // ... existing ...
    $schedule->command('subscriptions:process-orders')->dailyAt('08:00');
})
```

**Also register the command** in `bootstrap/app.php` `withCommands()` if explicit command registration is used, or rely on autodiscovery if available.

## Order Status

Check `app/Modules/Order/Domain/ValueObjects/OrderStatus.php` for the available statuses. Use the correct initial status for a new order — look at what `CreateOrderUseCase` sets. Likely `'pending'` or `'confirmed'`. Do NOT hardcode `'confirmed'` without checking.

## OrderItem Model/Table

Check `app/Models/OrderItem.php` for fillable fields. The column might be `product_variant_id` instead of `variant_id`. Match what the table actually has.

## Tests (`tests/Feature/SubscriptionCronTest.php`)

```php
<?php
use App\Console\Commands\ProcessSubscriptionOrdersCommand;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
uses(RefreshDatabase::class);

function cronUser(): User { return User::factory()->create(['role' => 'customer', 'is_active' => true]); }

function dueSubscription(User $user, ?string $nextOrderDate = null): Subscription
{
    $product = Product::factory()->create(['is_active' => true]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true, 'stock' => 10]);
    $address = Address::factory()->create(['user_id' => $user->id]);

    return Subscription::create([
        'user_id'         => $user->id,
        'product_id'      => $product->id,
        'variant_id'      => $variant->id,
        'quantity'        => 2,
        'address_id'      => $address->id,
        'plan'            => 'weekly',
        'discount_rate'   => 10.0,
        'status'          => 'active',
        'next_order_date' => $nextOrderDate ?? Carbon::today()->toDateString(),
        'start_date'      => Carbon::today()->subDays(7)->toDateString(),
    ]);
}

test('cron creates order for due subscription', function () {
    $user = cronUser();
    $sub  = dueSubscription($user);

    $this->artisan('subscriptions:process-orders')->assertSuccessful();

    $this->assertDatabaseCount('orders', 1);
    $sub->refresh();
    expect($sub->last_order_id)->not->toBeNull();
    expect($sub->next_order_date->toDateString())
        ->toBe(Carbon::today()->addDays(7)->toDateString());
});

test('cron does not create order for paused subscription', function () {
    $user = cronUser();
    $sub  = dueSubscription($user);
    $sub->update(['status' => 'paused', 'pause_until' => Carbon::today()->addDays(3)->toDateString()]);

    $this->artisan('subscriptions:process-orders')->assertSuccessful();

    $this->assertDatabaseCount('orders', 0);
});

test('cron does not create order for future next_order_date', function () {
    $user = cronUser();
    dueSubscription($user, Carbon::today()->addDays(5)->toDateString());

    $this->artisan('subscriptions:process-orders')->assertSuccessful();

    $this->assertDatabaseCount('orders', 0);
});

test('cron skips subscription with insufficient stock', function () {
    $user    = cronUser();
    $product = Product::factory()->create(['is_active' => true]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true, 'stock' => 0]);
    $address = Address::factory()->create(['user_id' => $user->id]);

    Subscription::create([
        'user_id'         => $user->id,
        'product_id'      => $product->id,
        'variant_id'      => $variant->id,
        'quantity'        => 2,
        'address_id'      => $address->id,
        'plan'            => 'weekly',
        'discount_rate'   => 10.0,
        'status'          => 'active',
        'next_order_date' => Carbon::today()->toDateString(),
        'start_date'      => Carbon::today()->subDays(7)->toDateString(),
    ]);

    $this->artisan('subscriptions:process-orders')->assertSuccessful();

    $this->assertDatabaseCount('orders', 0);
});
```

## Global Constraints

- Pest syntax, RefreshDatabase
- `php artisan test` must pass
- The cron MUST process multiple subscriptions in one run (loop, not single)
- One subscription failure must not abort the rest
- Do not use `CreateOrderUseCase` — it has minimum order amount validation that must not apply here
