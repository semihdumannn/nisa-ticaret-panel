# Task 6: FAQ Module

## Context

This is the last task of the backend API plan. It adds a simple FAQ module (read-only for users, admin-managed). No prior tasks need to be complete for this one — it is fully independent.

## Files to Create/Modify

| Action | Path |
|--------|------|
| Create | `database/migrations/2026_06_24_000006_create_faq_items_table.php` |
| Create | `app/Models/FaqItem.php` |
| Create | `app/Modules/Faq/Presentation/API/Controllers/FaqController.php` |
| Create | `app/Modules/Faq/Presentation/API/Controllers/AdminFaqController.php` |
| Create | `app/Modules/Faq/Presentation/API/Requests/StoreFaqRequest.php` |
| Create | `app/Modules/Faq/Presentation/API/Requests/UpdateFaqRequest.php` |
| Modify | `routes/api.php` |
| Create | `tests/Feature/FaqApiTest.php` |

No service provider or repository interface needed — this module is thin enough to use Eloquent directly in the controller.

## Database: `faq_items` table

```php
Schema::create('faq_items', function (Blueprint $table) {
    $table->id();
    $table->string('category', 100);
    $table->text('question');
    $table->text('answer');
    $table->integer('order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->index(['category', 'is_active', 'order']);
});
```

## `app/Models/FaqItem.php`

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FaqItem extends Model
{
    protected $fillable = ['category', 'question', 'answer', 'order', 'is_active'];
    protected $casts    = ['is_active' => 'boolean', 'order' => 'integer'];
}
```

## API Endpoint

### GET /api/v1/help/faq

**Auth:** Not required (public endpoint)

Groups active FAQ items by category, ordered by `order` ascending within each category.

Query:
```php
FaqItem::where('is_active', true)
    ->orderBy('category')
    ->orderBy('order')
    ->get()
    ->groupBy('category')
```

Response format:
```json
{
  "categories": [
    {
      "name": "Sipariş",
      "items": [
        {
          "id": 1,
          "question": "Siparişimi nasıl takip ederim?",
          "answer": "Siparişlerim ekranından..."
        }
      ]
    },
    {
      "name": "Teslimat",
      "items": [...]
    }
  ]
}
```

## Admin Endpoints (require `role:admin` middleware)

These go inside the existing admin middleware group in `routes/api.php`.

```
GET    /api/v1/admin/faq         → index (all items, paginated, with inactive)
POST   /api/v1/admin/faq         → store
PUT    /api/v1/admin/faq/{id}    → update
DELETE /api/v1/admin/faq/{id}    → destroy (hard delete — FAQs have no audit trail)
```

### StoreFaqRequest validation

```php
'category' => 'required|string|max:100',
'question' => 'required|string',
'answer'   => 'required|string',
'order'    => 'integer|min:0',
'is_active'=> 'boolean',
```

### UpdateFaqRequest

Same fields but all optional (`sometimes|`).

### AdminFaqController responses

- `GET /admin/faq` → paginated list, all items (not filtered by is_active)
  ```php
  FaqItem::orderBy('category')->orderBy('order')->paginate(20)
  ```
  Response: `{'data': [...], 'meta': {current_page, per_page, total, last_page}}`
  
- `POST` → 201 with the created item
- `PUT` → 200 with the updated item
- `DELETE` → 204 No Content

## Routes

```php
// Public (outside auth middleware)
Route::get('/help/faq', [FaqController::class, 'index'])->name('api.faq.index');

// Admin (inside existing admin middleware group)
Route::prefix('admin/faq')->name('api.admin.faq.')->group(function () {
    Route::get('/',        [AdminFaqController::class, 'index'])->name('index');
    Route::post('/',       [AdminFaqController::class, 'store'])->name('store');
    Route::put('/{id}',    [AdminFaqController::class, 'update'])->name('update');
    Route::delete('/{id}', [AdminFaqController::class, 'destroy'])->name('destroy');
});
```

**IMPORTANT:** The public `/help/faq` route goes OUTSIDE the sanctum auth middleware group since no auth is required. Look at how `GET /api/v1/products` is registered — if it's outside auth, put FAQ there too.

## Tests (`tests/Feature/FaqApiTest.php`)

```php
<?php
use App\Models\FaqItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);

test('anyone can list active faq items grouped by category', function () {
    FaqItem::create(['category' => 'Sipariş', 'question' => 'Q1', 'answer' => 'A1', 'order' => 1, 'is_active' => true]);
    FaqItem::create(['category' => 'Sipariş', 'question' => 'Q2', 'answer' => 'A2', 'order' => 2, 'is_active' => true]);
    FaqItem::create(['category' => 'Teslimat', 'question' => 'Q3', 'answer' => 'A3', 'order' => 1, 'is_active' => true]);
    FaqItem::create(['category' => 'Sipariş', 'question' => 'Q4', 'answer' => 'A4', 'order' => 3, 'is_active' => false]);

    $this->getJson('/api/v1/help/faq')
        ->assertOk()
        ->assertJsonStructure(['categories' => [['name', 'items' => [['id', 'question', 'answer']]]]])
        ->assertJsonCount(2, 'categories');
});

test('inactive faq items are excluded from public list', function () {
    FaqItem::create(['category' => 'Ödeme', 'question' => 'Q1', 'answer' => 'A1', 'is_active' => false]);

    $response = $this->getJson('/api/v1/help/faq')->assertOk();
    $this->assertEmpty($response->json('categories'));
});

test('admin can create faq item', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/admin/faq', [
            'category' => 'Hesap',
            'question' => 'Şifremi unuttum?',
            'answer'   => 'Şifremi unuttum butonuna tıklayın.',
            'order'    => 1,
        ])
        ->assertCreated()
        ->assertJsonPath('category', 'Hesap');
});

test('admin can delete faq item', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $item  = FaqItem::create(['category' => 'Test', 'question' => 'Q', 'answer' => 'A', 'is_active' => true]);

    $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/v1/admin/faq/{$item->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('faq_items', ['id' => $item->id]);
});

test('non-admin cannot access admin faq endpoints', function () {
    $user = User::factory()->create(['role' => 'customer', 'is_active' => true]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/admin/faq', ['category' => 'X', 'question' => 'Q', 'answer' => 'A'])
        ->assertForbidden();
});
```

## Global Constraints

- Pest syntax, RefreshDatabase
- `php artisan test` must pass
- No service provider needed — thin module, use Eloquent directly
- No repository interface needed — keep it simple
- Admin routes go inside the EXISTING admin middleware group (do not create a new one)
