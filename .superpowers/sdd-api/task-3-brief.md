# Task 3: Reviews Module (Full Feature)

## Context

No reviews functionality exists. This task creates the Reviews module following the project's DDD pattern.

Check `app/Modules/Campaign/` for reference on module structure.

## Files to Create/Modify

| Action | Path |
|--------|------|
| Create | `database/migrations/2026_06_24_000002_create_reviews_table.php` |
| Create | `database/migrations/2026_06_24_000003_add_is_reviewed_to_orders.php` |
| Create | `app/Models/Review.php` |
| Create | `app/Modules/Review/Domain/Contracts/ReviewRepositoryInterface.php` |
| Create | `app/Modules/Review/Domain/Exceptions/ReviewNotAllowedException.php` |
| Create | `app/Modules/Review/Application/UseCases/SubmitReviewUseCase.php` |
| Create | `app/Modules/Review/Application/UseCases/GetProductReviewsUseCase.php` |
| Create | `app/Modules/Review/Application/UseCases/GetOrderReviewStatusUseCase.php` |
| Create | `app/Modules/Review/Infrastructure/Repositories/EloquentReviewRepository.php` |
| Create | `app/Modules/Review/Presentation/API/Controllers/ReviewController.php` |
| Create | `app/Modules/Review/Presentation/API/Requests/SubmitReviewRequest.php` |
| Create | `app/Modules/Review/Presentation/API/Resources/ReviewResource.php` |
| Create | `app/Providers/ReviewModuleServiceProvider.php` |
| Modify | `bootstrap/providers.php` |
| Modify | `routes/api.php` |
| Modify | `app/Modules/Product/Presentation/API/Resources/ProductResource.php` (add average_rating, review_count) |
| Create | `tests/Feature/ReviewApiTest.php` |

## Database: `reviews` table

```php
Schema::create('reviews', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
    $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
    $table->tinyInteger('rating')->unsigned();
    $table->text('comment')->nullable();
    $table->json('tags')->nullable();
    $table->boolean('is_approved')->default(true);
    $table->timestamps();
    $table->unique(['user_id', 'order_id', 'product_id']);
    $table->index('product_id');
    $table->index(['order_id', 'user_id']);
});
```

## Database: add `is_reviewed` to orders

```php
Schema::table('orders', function (Blueprint $table) {
    $table->boolean('is_reviewed')->default(false)->after('notes');
});
```

## `app/Models/Review.php`

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = ['user_id', 'order_id', 'product_id', 'rating', 'comment', 'tags', 'is_approved'];
    protected $casts = ['tags' => 'array', 'is_approved' => 'boolean', 'rating' => 'integer'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
```

## ReviewRepositoryInterface

```php
interface ReviewRepositoryInterface
{
    public function create(array $data): Review;
    public function existsForUserOrderProduct(int $userId, int $orderId, int $productId): bool;
    public function paginateForProduct(int $productId, int $perPage, string $sort): LengthAwarePaginator;
    public function summaryForProduct(int $productId): array; // returns ['average' => float, 'total' => int, 'distribution' => array]
    public function reviewedProductIds(int $orderId, int $userId): array;
}
```

## SubmitReviewUseCase — Validation Rules

```php
// 1. Load the order — throw ReviewNotAllowedException('NOT_YOUR_ORDER') if order->customer_id != userId
// 2. Throw ReviewNotAllowedException('ORDER_NOT_DELIVERED') if order->status != 'delivered'
// 3. Throw ReviewNotAllowedException('PRODUCT_NOT_IN_ORDER') if product_id not in order->items->pluck('product_id')
// 4. Throw ReviewNotAllowedException('ALREADY_REVIEWED') if existsForUserOrderProduct returns true
// 5. Create the review
// 6. Check if all order items are now reviewed → if so, set order->is_reviewed = true
```

`ReviewNotAllowedException`:
```php
class ReviewNotAllowedException extends RuntimeException
{
    public function __construct(public readonly string $errorCode)
    {
        parent::__construct($errorCode, 422);
    }
}
```

## API Endpoints

### POST /api/v1/reviews (auth required)

Request body:
```json
{
  "order_id": 101,
  "product_id": 45,
  "rating": 5,
  "comment": "Çok hızlı geldi.",
  "tags": ["Hızlı teslimat"]
}
```

`SubmitReviewRequest` validation:
- `order_id` required integer exists:orders,id
- `product_id` required integer exists:products,id
- `rating` required integer between:1,5
- `comment` nullable string max:1000
- `tags` nullable array max:5
- `tags.*` string max:50

On `ReviewNotAllowedException`: return `response()->json(['error' => $e->errorCode], 422)`

Response 201: ReviewResource

### GET /api/v1/products/{id}/reviews (auth optional)

Query params: `page` (default 1), `per_page` (default 10), `sort` (`newest`|`highest`|`lowest`, default `newest`)

Sort mapping:
- `newest` → `created_at DESC`
- `highest` → `rating DESC, created_at DESC`
- `lowest` → `rating ASC, created_at DESC`

Response:
```json
{
  "data": [{"id": 55, "rating": 5, "comment": "...", "tags": [...], "user_name": "Ahmet Y.", "created_at": "..."}],
  "summary": {"average_rating": 4.7, "total_reviews": 23, "distribution": {"5":15,"4":5,"3":2,"2":1,"1":0}},
  "meta": {"current_page":1,"per_page":10,"total":23}
}
```

`user_name` format: first name + last initial. E.g. "Ahmet Yılmaz" → "Ahmet Y.". If single word name, just return the word.

### GET /api/v1/orders/{id}/review-status (auth required, own order only)

```json
{
  "order_id": 101,
  "can_review": true,
  "reviewed_product_ids": [33],
  "pending_product_ids": [45, 67]
}
```

`can_review = order.status == 'delivered'`
`reviewed_product_ids` = from `reviewedProductIds()` repository method
`pending_product_ids` = order item product_ids not in reviewed_product_ids

## ReviewResource

```php
return [
    'id'         => $this->id,
    'order_id'   => $this->order_id,
    'product_id' => $this->product_id,
    'rating'     => $this->rating,
    'comment'    => $this->comment,
    'tags'       => $this->tags ?? [],
    'created_at' => $this->created_at?->toISOString(),
];
```

## ProductResource Enhancement

Add to `app/Modules/Product/Presentation/API/Resources/ProductResource.php`:
```php
'average_rating' => (float) ($this->reviews_avg_rating ?? 0),
'review_count'   => (int) ($this->reviews_count ?? 0),
```

In `ProductController`, add `withAvg('reviews', 'rating')->withCount('reviews')` to product queries. Also add `reviews()` hasMany relationship to `app/Models/Product.php`.

## Routes (inside auth group)

```php
Route::post('/reviews', [ReviewController::class, 'store'])->name('api.reviews.store');
Route::get('/products/{productId}/reviews', [ReviewController::class, 'productReviews'])->name('api.reviews.product');
Route::get('/orders/{orderId}/review-status', [ReviewController::class, 'reviewStatus'])->name('api.reviews.status');
```

## Tests (tests/Feature/ReviewApiTest.php)

```php
<?php
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);

function reviewUser(): User { return User::factory()->create(['role' => 'customer', 'is_active' => true]); }

function deliveredOrder(User $customer, Product $product): Order
{
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'status'      => OrderStatus::DELIVERED->value,
    ]);
    $order->items()->create([
        'product_id'   => $product->id,
        'product_name' => $product->name,
        'quantity'     => 1,
        'unit_price'   => $product->price,
        'tax_rate'     => 0,
        'total'        => $product->price,
    ]);
    return $order;
}

test('customer can submit a review for delivered order', function () {
    $user    = reviewUser();
    $product = Product::factory()->create();
    $order   = deliveredOrder($user, $product);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/reviews', [
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'rating'     => 5,
            'comment'    => 'Harika!',
            'tags'       => ['Hızlı teslimat'],
        ])
        ->assertCreated()
        ->assertJsonPath('rating', 5);
});

test('cannot review non-delivered order', function () {
    $user    = reviewUser();
    $product = Product::factory()->create();
    $order   = Order::factory()->create(['customer_id' => $user->id, 'status' => OrderStatus::CONFIRMED->value]);
    $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'quantity' => 1, 'unit_price' => 10, 'tax_rate' => 0, 'total' => 10]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/reviews', ['order_id' => $order->id, 'product_id' => $product->id, 'rating' => 4])
        ->assertUnprocessable()
        ->assertJsonPath('error', 'ORDER_NOT_DELIVERED');
});

test('cannot review same product twice', function () {
    $user    = reviewUser();
    $product = Product::factory()->create();
    $order   = deliveredOrder($user, $product);
    Review::create(['user_id' => $user->id, 'order_id' => $order->id, 'product_id' => $product->id, 'rating' => 5]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/reviews', ['order_id' => $order->id, 'product_id' => $product->id, 'rating' => 4])
        ->assertUnprocessable()
        ->assertJsonPath('error', 'ALREADY_REVIEWED');
});

test('can get product reviews', function () {
    $user    = reviewUser();
    $product = Product::factory()->create();
    $order   = deliveredOrder($user, $product);
    Review::create(['user_id' => $user->id, 'order_id' => $order->id, 'product_id' => $product->id, 'rating' => 5, 'comment' => 'İyi']);

    $this->getJson("/api/v1/products/{$product->id}/reviews")
        ->assertOk()
        ->assertJsonStructure(['data', 'summary' => ['average_rating', 'total_reviews', 'distribution'], 'meta']);
});

test('can get order review status', function () {
    $user    = reviewUser();
    $product = Product::factory()->create();
    $order   = deliveredOrder($user, $product);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/orders/{$order->id}/review-status")
        ->assertOk()
        ->assertJsonStructure(['order_id', 'can_review', 'reviewed_product_ids', 'pending_product_ids']);
});
```

## Global Constraints

- Pest syntax, RefreshDatabase
- `php artisan test` must pass
- `is_reviewed` column added to orders (don't break existing order tests)
- `average_rating` and `review_count` in ProductResource (use `0` as default — existing tests check product structure)
