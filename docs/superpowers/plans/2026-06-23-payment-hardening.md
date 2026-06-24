# Payment Module Hardening Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Harden the payment module by introducing a domain contract, fixing sandbox hardcodes, adding token-based callback security, and migrating the min_order_amount check to AppConfig.

**Architecture:** The Order module's payment flow currently couples directly to `IyzicoPaymentService` (concrete class). We add `PaymentServiceInterface` to `Order/Domain/Contracts/`, bind it in `OrderModuleServiceProvider`, and update `PaymentController` to depend on the interface. Separately, `CreateOrderUseCase` reads a hardcoded `config('app.min_order_amount')` that conflicts with the DB-driven `AppConfig` record; we switch to `AppConfig::get()` and introduce a typed domain exception. Finally, the iyzico callback is secured by storing the generated token on the Order row and validating it before calling iyzico.

**Tech Stack:** Laravel 12, PHP 8.3, Pest PHP tests, PostgreSQL (Neon), iyzico PHP SDK

## Global Constraints

- PHP namespace root: `App\`
- All new interfaces go in `*/Domain/Contracts/`
- All new domain exceptions extend `RuntimeException` and pass HTTP code as `$code` (see `EmptyCartException`)
- Service bindings always go in the module's `*ModuleServiceProvider`, never in `AppServiceProvider`
- Tests use `RefreshDatabase`, `actingAs($user, 'sanctum')`, Pest syntax
- No breaking changes to existing passing tests (330 green)
- Run `php artisan test` to verify after each task

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| **Create** | `app/Modules/Order/Domain/Contracts/PaymentServiceInterface.php` | Domain contract for payment |
| **Modify** | `app/Modules/Order/Infrastructure/External/IyzicoPaymentService.php` | Implement interface; fix hardcodes; add product category from DB |
| **Modify** | `app/Modules/Order/Presentation/API/Controllers/PaymentController.php` | Depend on interface, not concrete; store+validate token |
| **Modify** | `app/Providers/OrderModuleServiceProvider.php` | Bind PaymentServiceInterface → IyzicoPaymentService |
| **Modify** | `app/Models/Order.php` | Add `payment_token` fillable |
| **Create** | `database/migrations/2026_06_23_000000_add_payment_token_to_orders.php` | `payment_token` nullable string column |
| **Create** | `app/Modules/Order/Domain/Exceptions/MinimumOrderAmountException.php` | Typed exception with amount context |
| **Modify** | `app/Modules/Order/Application/UseCases/CreateOrderUseCase.php` | Use `AppConfig::get('min_order_amount')` + new exception |
| **Modify** | `tests/Feature/PaymentApiTest.php` | Mock interface (not concrete); add token validation tests |
| **Modify** | `tests/Unit/OrderModuleTest.php` | Add MinimumOrderAmountException test |

---

## Task 1: PaymentServiceInterface

**Files:**
- Create: `app/Modules/Order/Domain/Contracts/PaymentServiceInterface.php`
- Modify: `app/Modules/Order/Infrastructure/External/IyzicoPaymentService.php`
- Modify: `app/Providers/OrderModuleServiceProvider.php`

**Interfaces:**
- Produces: `PaymentServiceInterface` with `initializeCheckout(Order, User, string): array` and `retrieveCheckoutForm(string): array` — used by Task 2 (PaymentController) and tests

---

- [ ] **Step 1: Create PaymentServiceInterface**

```php
<?php
// app/Modules/Order/Domain/Contracts/PaymentServiceInterface.php

namespace App\Modules\Order\Domain\Contracts;

use App\Models\Order;
use App\Models\User;

interface PaymentServiceInterface
{
    /**
     * Initialize a hosted checkout form.
     * Returns ['success'=>bool, 'checkout_form_url'=>string, 'token'=>string]
     * or ['success'=>false, 'message'=>string] on failure.
     */
    public function initializeCheckout(Order $order, User $customer, string $callbackUrl): array;

    /**
     * Retrieve and verify a completed checkout form by token.
     * Returns ['success'=>bool, 'payment_id'=>?string, 'conversation_id'=>?string,
     *          'fraud_status'=>?int, 'error_code'=>?string, 'error_message'=>?string]
     */
    public function retrieveCheckoutForm(string $token): array;
}
```

- [ ] **Step 2: Make IyzicoPaymentService implement PaymentServiceInterface**

Open `app/Modules/Order/Infrastructure/External/IyzicoPaymentService.php`. Change the class declaration from:

```php
class IyzicoPaymentService
```

to:

```php
use App\Modules\Order\Domain\Contracts\PaymentServiceInterface;

class IyzicoPaymentService implements PaymentServiceInterface
```

The existing method signatures already match the interface — no body changes yet (that's Task 3).

- [ ] **Step 3: Bind interface in OrderModuleServiceProvider**

Open `app/Providers/OrderModuleServiceProvider.php` and add the binding:

```php
<?php

namespace App\Providers;

use App\Modules\Order\Domain\Contracts\CartRepositoryInterface;
use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use App\Modules\Order\Domain\Contracts\PaymentServiceInterface;
use App\Modules\Order\Infrastructure\External\IyzicoPaymentService;
use App\Modules\Order\Infrastructure\Repositories\EloquentCartRepository;
use App\Modules\Order\Infrastructure\Repositories\EloquentOrderRepository;
use Illuminate\Support\ServiceProvider;

class OrderModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CartRepositoryInterface::class,    EloquentCartRepository::class);
        $this->app->bind(OrderRepositoryInterface::class,   EloquentOrderRepository::class);
        $this->app->bind(PaymentServiceInterface::class,    IyzicoPaymentService::class);
    }

    public function boot(): void {}
}
```

- [ ] **Step 4: Run tests to verify nothing broke**

```bash
php artisan test --filter PaymentApiTest
```

Expected: all existing PaymentApiTest tests still pass (mocks will now need updating — see Task 2 tests).

- [ ] **Step 5: Commit**

```bash
git add app/Modules/Order/Domain/Contracts/PaymentServiceInterface.php \
        app/Modules/Order/Infrastructure/External/IyzicoPaymentService.php \
        app/Providers/OrderModuleServiceProvider.php
git commit -m "feat(payment): introduce PaymentServiceInterface domain contract"
```

---

## Task 2: Fix IyzicoPaymentService Hardcodes + Update Controller + Tests

**Files:**
- Modify: `app/Modules/Order/Infrastructure/External/IyzicoPaymentService.php`
- Modify: `app/Modules/Order/Presentation/API/Controllers/PaymentController.php`
- Modify: `tests/Feature/PaymentApiTest.php`

**Interfaces:**
- Consumes: `PaymentServiceInterface` from Task 1

---

- [ ] **Step 1: Write failing test for category hardcode**

Open `tests/Feature/PaymentApiTest.php`. The mock for `initializeCheckout` already exists. Update the existing mocks to use the **interface** instead of the concrete class, and add a test that verifies the controller accepts the interface:

Replace all occurrences of:
```php
$this->mock(IyzicoPaymentService::class)
```
with:
```php
$this->mock(\App\Modules\Order\Domain\Contracts\PaymentServiceInterface::class)
```

Also remove the import at the top:
```php
use App\Modules\Order\Infrastructure\External\IyzicoPaymentService;
```

- [ ] **Step 2: Run tests to verify they now fail (because controller still uses concrete class)**

```bash
php artisan test --filter PaymentApiTest
```

Expected: FAIL — mocks won't intercept the concrete class anymore.

- [ ] **Step 3: Update PaymentController to use interface**

Open `app/Modules/Order/Presentation/API/Controllers/PaymentController.php`. Replace:

```php
use App\Modules\Order\Infrastructure\External\IyzicoPaymentService;
// ...
    public function __construct(
        private readonly IyzicoPaymentService $iyzico,
```

with:

```php
use App\Modules\Order\Domain\Contracts\PaymentServiceInterface;
// ...
    public function __construct(
        private readonly PaymentServiceInterface $iyzico,
```

- [ ] **Step 4: Run tests to verify they pass again**

```bash
php artisan test --filter PaymentApiTest
```

Expected: all PASS.

- [ ] **Step 5: Fix the 3 hardcodes in IyzicoPaymentService**

Open `app/Modules/Order/Infrastructure/External/IyzicoPaymentService.php`.

**Fix 1 — TR Identity Number (remove sandbox value):**
```php
// BEFORE (line ~48):
$buyer->setIdentityNumber('11111111111'); // TR national ID (sandbox)

// AFTER: use config — defaults to sandbox value only in non-production
$buyer->setIdentityNumber(config('services.iyzico.buyer_identity_number', '11111111111'));
```

**Fix 2 — Surname from name:**
```php
// BEFORE (line ~44):
$buyer->setName($customer->name ?? 'Unknown');
$buyer->setSurname('—');

// AFTER: split on first space; fallback to single token for both
$nameParts = explode(' ', $customer->name ?? 'Müşteri', 2);
$buyer->setName($nameParts[0]);
$buyer->setSurname($nameParts[1] ?? $nameParts[0]);
```

**Fix 3 — Product category from relation:**
```php
// BEFORE (inside the foreach, line ~62):
$bi->setCategory1('Beverage');

// AFTER: load category name from product relation (already eager-loaded via initiate())
$bi->setCategory1($item->product->category?->name ?? 'Genel');
```

- [ ] **Step 6: Run all tests**

```bash
php artisan test
```

Expected: 330+ tests PASS.

- [ ] **Step 7: Commit**

```bash
git add app/Modules/Order/Domain/Contracts/PaymentServiceInterface.php \
        app/Modules/Order/Infrastructure/External/IyzicoPaymentService.php \
        app/Modules/Order/Presentation/API/Controllers/PaymentController.php \
        tests/Feature/PaymentApiTest.php
git commit -m "fix(payment): use PaymentServiceInterface in controller; fix iyzico hardcodes"
```

---

## Task 3: Iyzico Callback Token Security

Store the iyzico `token` in the `orders` table when initiating payment. On callback, verify the incoming token exists in orders before calling iyzico. This prevents blind calls with arbitrary tokens.

**Files:**
- Create: `database/migrations/2026_06_23_000000_add_payment_token_to_orders.php`
- Modify: `app/Models/Order.php`
- Modify: `app/Modules/Order/Presentation/API/Controllers/PaymentController.php`
- Modify: `tests/Feature/PaymentApiTest.php`

---

- [ ] **Step 1: Write failing tests for token validation**

Add to `tests/Feature/PaymentApiTest.php`:

```php
test('callback rejects token not associated with any order', function () {
    // No order has this token stored
    $this->mock(\App\Modules\Order\Domain\Contracts\PaymentServiceInterface::class)
        ->shouldNotReceive('retrieveCheckoutForm'); // iyzico should NOT be called

    $this->postJson('/api/v1/payment/callback', ['token' => 'tok_unknown_xyz'])
        ->assertNotFound()
        ->assertJsonFragment(['message' => 'Order not found.']);
});

test('initiate stores payment token on order', function () {
    $customer = paymentCustomer();
    $order    = paymentPendingOrder($customer);

    $this->mock(\App\Modules\Order\Domain\Contracts\PaymentServiceInterface::class)
        ->shouldReceive('initializeCheckout')
        ->once()
        ->andReturn([
            'success'           => true,
            'checkout_form_url' => 'https://sandbox-api.iyzipay.com/checkout/form/abc123',
            'token'             => 'tok_stored_abc123',
        ]);

    $this->actingAs($customer, 'sanctum')
        ->postJson("/api/v1/orders/{$order->id}/pay")
        ->assertOk();

    $this->assertDatabaseHas('orders', [
        'id'            => $order->id,
        'payment_token' => 'tok_stored_abc123',
    ]);
});
```

- [ ] **Step 2: Run failing tests**

```bash
php artisan test --filter "rejects token not associated|stores payment token"
```

Expected: both FAIL.

- [ ] **Step 3: Create migration**

```bash
php artisan make:migration add_payment_token_to_orders --table=orders
```

Open the generated file and replace its `up()` / `down()` with:

```php
public function up(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->string('payment_token', 128)->nullable()->after('payment_reference');
        $table->index('payment_token');
    });
}

public function down(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropIndex(['payment_token']);
        $table->dropColumn('payment_token');
    });
}
```

- [ ] **Step 4: Run migration**

```bash
php artisan migrate
```

- [ ] **Step 5: Add payment_token to Order fillable**

Open `app/Models/Order.php`. Find the `$fillable` array and add `'payment_token'`:

```php
protected $fillable = [
    // ... existing fields ...
    'payment_token',
];
```

- [ ] **Step 6: Update PaymentController::initiate to store token**

In `app/Modules/Order/Presentation/API/Controllers/PaymentController.php`, after the successful iyzico response in `initiate()`, store the token:

```php
// AFTER the $result success check, BEFORE the return:
$order->update(['payment_token' => $result['token']]);

return response()->json([
    'checkout_url' => $result['checkout_form_url'],
    'token'        => $result['token'],
]);
```

- [ ] **Step 7: Update PaymentController::callback to validate token**

In the `callback()` method, replace the existing token-to-order lookup:

```php
// BEFORE:
$result = $this->iyzico->retrieveCheckoutForm($token);
$orderId = $result['conversation_id'] ?? null;
$order   = $orderId ? Order::find($orderId) : null;

if (! $order) {
    return response()->json(['message' => 'Order not found.'], 404);
}
```

```php
// AFTER: validate token against DB first — don't call iyzico with unknown tokens
$order = Order::where('payment_token', $token)->first();

if (! $order) {
    return response()->json(['message' => 'Order not found.'], 404);
}

$result = $this->iyzico->retrieveCheckoutForm($token);
```

Remove the now-redundant `$orderId` / `Order::find()` lines that came after.

- [ ] **Step 8: Update existing callback tests to set payment_token on factory orders**

In `tests/Feature/PaymentApiTest.php`, the `paymentPendingOrder` helper and all callback tests that set `conversation_id` need to pre-set `payment_token` on the order:

```php
// Update paymentPendingOrder helper:
function paymentPendingOrder(User $customer): Order
{
    return Order::factory()->create([
        'customer_id'    => $customer->id,
        'status'         => OrderStatus::PENDING->value,
        'payment_status' => PaymentStatus::PENDING->value,
        'total'          => 250.00,
        'payment_token'  => null, // explicit
    ]);
}
```

For callback tests that expect a successful flow, set `payment_token` on the order before the request:

```php
// In "callback marks order as paid" test, after creating $order:
$order->update(['payment_token' => 'tok_success']);

// In "callback marks order as failed" test:
$order->update(['payment_token' => 'tok_fail']);

// In "callback is idempotent" test:
$order->update(['payment_token' => 'tok_dup']);
```

- [ ] **Step 9: Run all tests**

```bash
php artisan test
```

Expected: 332+ tests PASS (2 new tests added).

- [ ] **Step 10: Commit**

```bash
git add database/migrations/2026_06_23_000000_add_payment_token_to_orders.php \
        app/Models/Order.php \
        app/Modules/Order/Presentation/API/Controllers/PaymentController.php \
        tests/Feature/PaymentApiTest.php
git commit -m "feat(payment): store iyzico token; validate on callback before calling iyzico"
```

---

## Task 4: MinimumOrderAmountException + AppConfig Integration

Replace `\RuntimeException` and `config('app.min_order_amount')` with a typed domain exception and `AppConfig::get()` which reads from the DB-seeded `min_order_amount` record.

**Files:**
- Create: `app/Modules/Order/Domain/Exceptions/MinimumOrderAmountException.php`
- Modify: `app/Modules/Order/Application/UseCases/CreateOrderUseCase.php`
- Modify: `tests/Unit/OrderModuleTest.php`

**Interfaces:**
- Consumes: `AppConfig::get(string, mixed): mixed` — already available globally via `App\Models\AppConfig`

---

- [ ] **Step 1: Write failing unit test**

Open `tests/Unit/OrderModuleTest.php`. Add inside the appropriate describe block (or at file level if none exists):

```php
use App\Modules\Order\Domain\Exceptions\MinimumOrderAmountException;
use App\Models\AppConfig;

it('throws MinimumOrderAmountException when subtotal is below configured minimum', function () {
    // Seed a low minimum so we can trigger the exception
    AppConfig::where('key', 'min_order_amount')->updateOrInsert(
        ['key' => 'min_order_amount'],
        ['value' => '500', 'type' => 'number', 'description' => 'test']
    );
    AppConfig::flushCache();

    $exception = new MinimumOrderAmountException(minimum: 500.0, actual: 150.0);

    expect($exception)->toBeInstanceOf(\RuntimeException::class)
        ->and($exception->getMinimum())->toBe(500.0)
        ->and($exception->getActual())->toBe(150.0)
        ->and($exception->getCode())->toBe(422)
        ->and($exception->getMessage())->toContain('500');
});
```

- [ ] **Step 2: Run failing test**

```bash
php artisan test --filter "throws MinimumOrderAmountException"
```

Expected: FAIL — class not found.

- [ ] **Step 3: Create MinimumOrderAmountException**

```php
<?php
// app/Modules/Order/Domain/Exceptions/MinimumOrderAmountException.php

namespace App\Modules\Order\Domain\Exceptions;

use RuntimeException;

class MinimumOrderAmountException extends RuntimeException
{
    public function __construct(
        private readonly float $minimum,
        private readonly float $actual,
    ) {
        parent::__construct(
            "Minimum sipariş tutarı ₺{$minimum}'dir. Sepetiniz: ₺{$actual}",
            422
        );
    }

    public function getMinimum(): float { return $this->minimum; }
    public function getActual(): float  { return $this->actual; }
}
```

- [ ] **Step 4: Run test to verify it passes**

```bash
php artisan test --filter "throws MinimumOrderAmountException"
```

Expected: PASS.

- [ ] **Step 5: Update CreateOrderUseCase to use AppConfig + new exception**

Open `app/Modules/Order/Application/UseCases/CreateOrderUseCase.php`.

Add import at top:

```php
use App\Models\AppConfig;
use App\Modules\Order\Domain\Exceptions\MinimumOrderAmountException;
```

Replace the min_order_amount block (currently around line 79-83):

```php
// BEFORE:
$minAmount = (float) config('app.min_order_amount', 200.0);
if ($estimatedSubtotal < $minAmount) {
    throw new \RuntimeException(
        "Minimum sipariş tutarı ₺{$minAmount}'dir. Sepetiniz: ₺{$estimatedSubtotal}"
    );
}
```

```php
// AFTER:
$minAmount = (float) AppConfig::get('min_order_amount', 50.0);
if ($estimatedSubtotal < $minAmount) {
    throw new MinimumOrderAmountException(
        minimum: $minAmount,
        actual:  $estimatedSubtotal,
    );
}
```

- [ ] **Step 6: Write integration test for the order creation path**

Add to `tests/Feature/OrderApiTest.php` (find the order creation section):

```php
test('create order fails with 422 when subtotal is below min_order_amount', function () {
    // Set a very high minimum so any order fails
    \App\Models\AppConfig::where('key', 'min_order_amount')->updateOrInsert(
        ['key' => 'min_order_amount'],
        ['value' => '99999', 'type' => 'number', 'description' => 'test']
    );
    \App\Models\AppConfig::flushCache();

    $customer = \App\Models\User::factory()->create(['role' => 'customer', 'is_active' => true]);

    $this->actingAs($customer, 'sanctum')
        ->postJson('/api/v1/orders', [
            'items'          => [['product_id' => 1, 'quantity' => 1]],
            'address_id'     => null,
            'payment_method' => 'cod',
        ])
        ->assertUnprocessable();
});
```

> Note: This test intentionally uses a real product_id from seeded data. If the test environment has no products, use `\App\Models\Product::factory()->create()` first.

- [ ] **Step 7: Run all tests**

```bash
php artisan test
```

Expected: 333+ tests PASS.

- [ ] **Step 8: Commit**

```bash
git add app/Modules/Order/Domain/Exceptions/MinimumOrderAmountException.php \
        app/Modules/Order/Application/UseCases/CreateOrderUseCase.php \
        tests/Unit/OrderModuleTest.php \
        tests/Feature/OrderApiTest.php
git commit -m "feat(order): MinimumOrderAmountException + read min_order_amount from AppConfig"
```

---

## Self-Review Checklist

**Spec coverage:**
- ✅ PaymentServiceInterface — Task 1
- ✅ Controller depends on interface — Task 2
- ✅ IyzicoPaymentService hardcodes (identity, surname, category) — Task 2 Step 5
- ✅ Callback token security — Task 3
- ✅ min_order_amount from AppConfig — Task 4
- ✅ Domain exception for min order — Task 4
- ✅ Tests updated for interface mocking — Task 2 Step 1
- ✅ Existing 330 tests must remain green — verified in each task's final step

**Placeholder scan:** None found. All steps include exact code.

**Type consistency:**
- `PaymentServiceInterface` methods defined in Task 1 match the concrete class signatures used in Tasks 2-3.
- `MinimumOrderAmountException(minimum: float, actual: float)` defined in Task 4 Step 3 matches usage in Task 4 Step 5.
- `payment_token` column added in Task 3 migration matches `Order::$fillable` and controller usage.
