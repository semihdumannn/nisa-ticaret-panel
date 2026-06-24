# Task 2: Favorites Module (Full Feature)

## Context

No favorites functionality exists yet. This task creates a complete Favorites module following the project's modular DDD pattern.

Reference for module structure: look at `app/Modules/Campaign/` — it has Domain/Application/Infrastructure/Presentation layers. Follow the same pattern.

## Files to Create

| Action | Path |
|--------|------|
| Create | `database/migrations/2026_06_24_000001_create_favorites_table.php` |
| Create | `app/Models/Favorite.php` |
| Create | `app/Modules/Favorite/Domain/Contracts/FavoriteRepositoryInterface.php` |
| Create | `app/Modules/Favorite/Application/UseCases/AddFavoriteUseCase.php` |
| Create | `app/Modules/Favorite/Application/UseCases/RemoveFavoriteUseCase.php` |
| Create | `app/Modules/Favorite/Application/UseCases/ListFavoritesUseCase.php` |
| Create | `app/Modules/Favorite/Infrastructure/Repositories/EloquentFavoriteRepository.php` |
| Create | `app/Modules/Favorite/Presentation/API/Controllers/FavoriteController.php` |
| Create | `app/Modules/Favorite/Presentation/API/Resources/FavoriteResource.php` |
| Create | `app/Providers/FavoriteModuleServiceProvider.php` |
| Modify | `bootstrap/providers.php` (register new service provider) |
| Modify | `routes/api.php` |
| Modify | `app/Modules/Product/Presentation/API/Resources/ProductResource.php` (add `is_favorited`) |
| Create | `tests/Feature/FavoriteApiTest.php` |

## Database: `favorites` table

```php
Schema::create('favorites', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
    $table->timestamp('created_at')->useCurrent();
    $table->unique(['user_id', 'product_id']);
    $table->index('user_id');
});
```

No `updated_at` needed (favorites are created/deleted, never updated).

## `app/Models/Favorite.php`

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'product_id'];
    protected $casts = ['created_at' => 'datetime'];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
```

## FavoriteRepositoryInterface

```php
interface FavoriteRepositoryInterface
{
    public function listForUser(int $userId, int $perPage = 20): LengthAwarePaginator;
    public function findByUserAndProduct(int $userId, int $productId): ?Favorite;
    public function add(int $userId, int $productId): Favorite;
    public function removeById(int $id, int $userId): bool;       // returns false if not found or not owned
    public function removeByProduct(int $userId, int $productId): bool;
    public function isProductFavoritedByUser(int $productId, int $userId): bool;
    public function getFavoritedProductIds(int $userId, array $productIds): array; // for batch check
}
```

## API Endpoints

### GET /api/v1/favorites

Paginated list with product eager-loaded.

```php
public function index(Request $request): JsonResponse
{
    $favorites = $this->listFavorites->execute($request->user()->id, (int) $request->get('per_page', 20));
    return response()->json([
        'data' => FavoriteResource::collection($favorites),
        'meta' => [
            'current_page' => $favorites->currentPage(),
            'per_page'     => $favorites->perPage(),
            'total'        => $favorites->total(),
            'last_page'    => $favorites->lastPage(),
        ],
    ]);
}
```

### POST /api/v1/favorites

```php
// Request body: {"product_id": 45}
// On duplicate unique constraint: return 409 {"error": "ALREADY_FAVORITED"}
// On success: 201 with FavoriteResource
```

### DELETE /api/v1/favorites/{id}

Remove by favorites.id. Only owner can delete. 204 on success, 404 if not found.

### DELETE /api/v1/favorites/by-product/{product_id}

Remove by product_id for current user. 204 on success (idempotent — 204 even if not favorited).

**IMPORTANT route ordering:** Register `by-product/{product_id}` BEFORE `{id}` to avoid Laravel treating "by-product" as an id.

### FavoriteResource

```php
return [
    'id'         => $this->id,
    'product_id' => $this->product_id,
    'created_at' => $this->created_at?->toISOString(),
    'product'    => $this->whenLoaded('product', fn() => [
        'id'                => $this->product->id,
        'name'              => $this->product->name,
        'image_url'         => $this->product->image_url ?? null,
        'primary_price'     => (float) $this->product->price,
        'is_active'         => (bool) $this->product->is_active,
    ]),
];
```

## ProductResource — add is_favorited

In `app/Modules/Product/Presentation/API/Resources/ProductResource.php`, add to the array:

```php
'is_favorited' => (bool) ($this->additional['is_favorited'] ?? false),
```

The controller sets this via `->additional(['is_favorited' => ...])`. Look at how `ProductController` returns products — check if it already has a pattern for additional data. If not, use:

```php
// In ProductController::index() and show(), after fetching product(s):
$userId = $request->user()?->id;
if ($userId) {
    $favoriteIds = app(FavoriteRepositoryInterface::class)->getFavoritedProductIds($userId, [$product->id]);
    return new ProductResource($product)->additional(['is_favorited' => in_array($product->id, $favoriteIds)]);
}
return new ProductResource($product);
```

For the list endpoint, use `getFavoritedProductIds($userId, $products->pluck('id')->toArray())` in a single query.

## FavoriteModuleServiceProvider

```php
<?php
namespace App\Providers;
use App\Modules\Favorite\Domain\Contracts\FavoriteRepositoryInterface;
use App\Modules\Favorite\Infrastructure\Repositories\EloquentFavoriteRepository;
use Illuminate\Support\ServiceProvider;

class FavoriteModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FavoriteRepositoryInterface::class, EloquentFavoriteRepository::class);
    }
    public function boot(): void {}
}
```

Register it in `bootstrap/providers.php`.

## Routes (add to routes/api.php, inside sanctum auth middleware group)

```php
Route::prefix('favorites')->name('api.favorites.')->group(function () {
    Route::get('/',                        [FavoriteController::class, 'index'])->name('index');
    Route::post('/',                       [FavoriteController::class, 'store'])->name('store');
    Route::delete('/by-product/{productId}', [FavoriteController::class, 'destroyByProduct'])->name('destroy-by-product');
    Route::delete('/{id}',                 [FavoriteController::class, 'destroy'])->name('destroy');
});
```

## Tests (create tests/Feature/FavoriteApiTest.php)

```php
<?php
use App\Models\Favorite;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);

function favoriteUser(): User
{
    return User::factory()->create(['role' => 'customer', 'is_active' => true]);
}

test('user can list favorites', function () {
    $user    = favoriteUser();
    $product = Product::factory()->create();
    Favorite::create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/favorites')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta']);
});

test('user can add a favorite', function () {
    $user    = favoriteUser();
    $product = Product::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/favorites', ['product_id' => $product->id])
        ->assertCreated()
        ->assertJsonPath('product_id', $product->id);
});

test('adding duplicate favorite returns 409', function () {
    $user    = favoriteUser();
    $product = Product::factory()->create();
    Favorite::create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/favorites', ['product_id' => $product->id])
        ->assertStatus(409)
        ->assertJsonPath('error', 'ALREADY_FAVORITED');
});

test('user can remove favorite by id', function () {
    $user    = favoriteUser();
    $product = Product::factory()->create();
    $fav     = Favorite::create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/favorites/{$fav->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('favorites', ['id' => $fav->id]);
});

test('user can remove favorite by product id', function () {
    $user    = favoriteUser();
    $product = Product::factory()->create();
    Favorite::create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/favorites/by-product/{$product->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('favorites', ['user_id' => $user->id, 'product_id' => $product->id]);
});

test('user cannot delete another users favorite', function () {
    $user1   = favoriteUser();
    $user2   = favoriteUser();
    $product = Product::factory()->create();
    $fav     = Favorite::create(['user_id' => $user1->id, 'product_id' => $product->id]);

    $this->actingAs($user2, 'sanctum')
        ->deleteJson("/api/v1/favorites/{$fav->id}")
        ->assertNotFound();
});

test('product list includes is_favorited field', function () {
    $user    = favoriteUser();
    $product = Product::factory()->create(['is_active' => true]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/products')
        ->assertOk()
        ->assertJsonStructure(['data' => [['is_favorited']]]);
});
```

## Global Constraints

- Pest syntax, RefreshDatabase
- Run `php artisan migrate` after creating migration
- `php artisan test` must pass (333 + 7 new = 340+)
- Follow existing module DDD pattern
- No breaking changes to existing tests
