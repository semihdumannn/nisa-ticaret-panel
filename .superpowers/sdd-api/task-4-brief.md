# Task 4: Subscriptions Module (CRUD Endpoints)

## Context

No subscription functionality exists. This task creates the Subscription module with CRUD API endpoints. The cron job (auto-order) is a separate Task 5.

Check `app/Modules/Order/` for reference on module structure and how to handle order creation.

## Files to Create

| Action | Path |
|--------|------|
| Create | `database/migrations/2026_06_24_000004_create_subscriptions_table.php` |
| Create | `app/Models/Subscription.php` |
| Create | `app/Modules/Subscription/Domain/Contracts/SubscriptionRepositoryInterface.php` |
| Create | `app/Modules/Subscription/Domain/Exceptions/SubscriptionException.php` |
| Create | `app/Modules/Subscription/Application/UseCases/CreateSubscriptionUseCase.php` |
| Create | `app/Modules/Subscription/Application/UseCases/UpdateSubscriptionUseCase.php` |
| Create | `app/Modules/Subscription/Application/UseCases/CancelSubscriptionUseCase.php` |
| Create | `app/Modules/Subscription/Application/UseCases/ListSubscriptionsUseCase.php` |
| Create | `app/Modules/Subscription/Infrastructure/Repositories/EloquentSubscriptionRepository.php` |
| Create | `app/Modules/Subscription/Presentation/API/Controllers/SubscriptionController.php` |
| Create | `app/Modules/Subscription/Presentation/API/Requests/CreateSubscriptionRequest.php` |
| Create | `app/Modules/Subscription/Presentation/API/Requests/UpdateSubscriptionRequest.php` |
| Create | `app/Modules/Subscription/Presentation/API/Resources/SubscriptionResource.php` |
| Create | `app/Providers/SubscriptionModuleServiceProvider.php` |
| Modify | `bootstrap/providers.php` |
| Modify | `routes/api.php` |
| Create | `tests/Feature/SubscriptionApiTest.php` |

## Database: `subscriptions` table

```php
Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('product_id')->constrained('products');
    $table->foreignId('variant_id')->constrained('product_variants');
    $table->integer('quantity')->default(1);
    $table->foreignId('address_id')->constrained('addresses');
    $table->enum('plan', ['weekly', 'biweekly', 'monthly']);
    $table->decimal('discount_rate', 5, 2);
    $table->enum('status', ['active', 'paused', 'cancelled'])->default('active');
    $table->date('next_order_date');
    $table->foreignId('last_order_id')->nullable()->constrained('orders')->nullOnDelete();
    $table->date('start_date');
    $table->date('pause_until')->nullable();
    $table->dateTime('cancelled_at')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
    $table->index('user_id');
    $table->index(['status', 'next_order_date']);
});
```

## `app/Models/Subscription.php`

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id', 'product_id', 'variant_id', 'quantity', 'address_id',
        'plan', 'discount_rate', 'status', 'next_order_date', 'last_order_id',
        'start_date', 'pause_until', 'cancelled_at', 'notes',
    ];
    protected $casts = [
        'next_order_date' => 'date',
        'start_date'      => 'date',
        'pause_until'     => 'date',
        'cancelled_at'    => 'datetime',
        'discount_rate'   => 'float',
        'quantity'        => 'integer',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class); }
    public function address(): BelongsTo { return $this->belongsTo(Address::class); }
    public function lastOrder(): BelongsTo { return $this->belongsTo(Order::class, 'last_order_id'); }
}
```

## Plan Discount Rates

```php
const PLAN_DISCOUNTS = ['weekly' => 10.0, 'biweekly' => 8.0, 'monthly' => 5.0];
```

## SubscriptionRepositoryInterface

```php
interface SubscriptionRepositoryInterface
{
    public function create(array $data): Subscription;
    public function update(Subscription $sub, array $data): Subscription;
    public function findByIdAndUser(int $id, int $userId): ?Subscription;
    public function listForUser(int $userId, array $statuses): Collection;
    public function findDueToday(): Collection; // for cron — Task 5
}
```

## CreateSubscriptionUseCase — Validation

```php
// 1. Load variant — throw SubscriptionException('VARIANT_NOT_FOUND') if missing
// 2. Check variant->is_active — throw SubscriptionException('VARIANT_INACTIVE')
// 3. Validate address belongs to user — throw SubscriptionException('ADDRESS_NOT_YOURS')
// 4. start_date >= today — throw SubscriptionException('INVALID_START_DATE')
// 5. Set discount_rate from PLAN_DISCOUNTS[$plan]
// 6. Set next_order_date = start_date
// 7. Create and return
```

## UpdateSubscriptionUseCase — Rules

```php
// - Only own subscription (findByIdAndUser)
// - Not cancelled
// - If status = 'paused': pause_until required, must be within 90 days
// - If status = 'active': clear pause_until, recalculate next_order_date from today
// - If plan changes: update discount_rate automatically
// - Do NOT allow changing product_id or variant_id (reject silently — just ignore those fields)
```

## CancelSubscriptionUseCase

```php
// - Only own subscription
// - Set status = 'cancelled', cancelled_at = now(), next_order_date = null (set to today as placeholder)
```

## API Endpoints

### GET /api/v1/subscriptions

Query param: `status` — `active|paused|cancelled|all` (default: `active,paused` = both)

```php
$statuses = match($request->get('status', 'active_paused')) {
    'active'        => ['active'],
    'paused'        => ['paused'],
    'cancelled'     => ['cancelled'],
    'all'           => ['active', 'paused', 'cancelled'],
    default         => ['active', 'paused'],
};
```

### POST /api/v1/subscriptions

CreateSubscriptionRequest:
- `product_id` required integer exists:products,id
- `variant_id` required integer exists:product_variants,id
- `quantity` required integer min:1
- `address_id` required integer exists:addresses,id
- `plan` required in:weekly,biweekly,monthly
- `start_date` required date after_or_equal:today
- `notes` nullable string max:500

Response 201: SubscriptionResource

### PUT /api/v1/subscriptions/{id}

UpdateSubscriptionRequest (all nullable):
- `plan` in:weekly,biweekly,monthly
- `quantity` integer min:1
- `address_id` integer exists:addresses,id
- `status` in:active,paused
- `pause_until` date (required if status=paused)
- `notes` string max:500

Response 200: SubscriptionResource

### DELETE /api/v1/subscriptions/{id}

Response 204 No Content.

## SubscriptionResource

```php
return [
    'id'               => $this->id,
    'plan'             => $this->plan,
    'quantity'         => $this->quantity,
    'discount_rate'    => $this->discount_rate,
    'discounted_price' => $this->whenLoaded('variant', function () {
        $base = (float) $this->variant->price;
        return round($base * $this->quantity * (1 - $this->discount_rate / 100), 2);
    }),
    'status'           => $this->status,
    'next_order_date'  => $this->next_order_date?->toDateString(),
    'pause_until'      => $this->pause_until?->toDateString(),
    'start_date'       => $this->start_date?->toDateString(),
    'notes'            => $this->notes,
    'created_at'       => $this->created_at?->toISOString(),
    'product'          => $this->whenLoaded('product', fn() => [
        'id'        => $this->product->id,
        'name'      => $this->product->name,
        'image_url' => $this->product->image_url ?? null,
    ]),
    'variant'          => $this->whenLoaded('variant', fn() => [
        'id'    => $this->variant->id,
        'name'  => $this->variant->name,
        'price' => (float) $this->variant->price,
    ]),
    'address'          => $this->whenLoaded('address', fn() => [
        'id'           => $this->address->id,
        'title'        => $this->address->title,
        'full_address' => $this->address->full_address,
    ]),
];
```

## Routes (inside auth group)

```php
Route::prefix('subscriptions')->name('api.subscriptions.')->group(function () {
    Route::get('/',    [SubscriptionController::class, 'index'])->name('index');
    Route::post('/',   [SubscriptionController::class, 'store'])->name('store');
    Route::put('/{id}',    [SubscriptionController::class, 'update'])->name('update');
    Route::delete('/{id}', [SubscriptionController::class, 'destroy'])->name('destroy');
});
```

## Tests (tests/Feature/SubscriptionApiTest.php)

```php
<?php
use App\Models\Address;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);

function subUser(): User { return User::factory()->create(['role' => 'customer', 'is_active' => true]); }

function subFixtures(User $user): array
{
    $product = Product::factory()->create(['is_active' => true]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true]);
    $address = Address::factory()->create(['user_id' => $user->id]);
    return compact('product', 'variant', 'address');
}

test('user can create a subscription', function () {
    $user = subUser();
    ['product' => $product, 'variant' => $variant, 'address' => $address] = subFixtures($user);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/subscriptions', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity'   => 2,
            'address_id' => $address->id,
            'plan'       => 'weekly',
            'start_date' => now()->addDay()->toDateString(),
        ])
        ->assertCreated()
        ->assertJsonPath('plan', 'weekly')
        ->assertJsonPath('discount_rate', 10.0);
});

test('user can list their subscriptions', function () {
    $user = subUser();
    ['product' => $product, 'variant' => $variant, 'address' => $address] = subFixtures($user);
    Subscription::create([
        'user_id' => $user->id, 'product_id' => $product->id, 'variant_id' => $variant->id,
        'quantity' => 1, 'address_id' => $address->id, 'plan' => 'monthly',
        'discount_rate' => 5.0, 'status' => 'active',
        'next_order_date' => now()->addDays(30)->toDateString(),
        'start_date' => now()->toDateString(),
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/subscriptions')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

test('user can cancel a subscription', function () {
    $user = subUser();
    ['product' => $product, 'variant' => $variant, 'address' => $address] = subFixtures($user);
    $sub = Subscription::create([
        'user_id' => $user->id, 'product_id' => $product->id, 'variant_id' => $variant->id,
        'quantity' => 1, 'address_id' => $address->id, 'plan' => 'weekly',
        'discount_rate' => 10.0, 'status' => 'active',
        'next_order_date' => now()->addDays(7)->toDateString(),
        'start_date' => now()->toDateString(),
    ]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/subscriptions/{$sub->id}")
        ->assertNoContent();

    $this->assertDatabaseHas('subscriptions', ['id' => $sub->id, 'status' => 'cancelled']);
});

test('user cannot access another users subscription', function () {
    $user1 = subUser();
    $user2 = subUser();
    ['product' => $product, 'variant' => $variant, 'address' => $address] = subFixtures($user1);
    $sub = Subscription::create([
        'user_id' => $user1->id, 'product_id' => $product->id, 'variant_id' => $variant->id,
        'quantity' => 1, 'address_id' => $address->id, 'plan' => 'monthly',
        'discount_rate' => 5.0, 'status' => 'active',
        'next_order_date' => now()->addDays(30)->toDateString(),
        'start_date' => now()->toDateString(),
    ]);

    $this->actingAs($user2, 'sanctum')
        ->deleteJson("/api/v1/subscriptions/{$sub->id}")
        ->assertNotFound();
});
```

NOTE: `ProductVariant::factory()` and `Address::factory()` may need to be created if they don't exist. Check `database/factories/` first.

## Global Constraints

- Pest syntax, RefreshDatabase
- `php artisan test` must pass
- `ProductVariant` model is at `app/Models/ProductVariant.php` — check it for the `price` field name
- Follow DDD pattern with service provider binding
