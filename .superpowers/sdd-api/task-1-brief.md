# Task 1: Coupon List Endpoint + Admin CRUD

## Context

The Campaign module already has `ValidateCouponUseCase`, `CouponController` (validate only), `CouponRepositoryInterface`, `EloquentCouponRepository`. The `Coupon` model is at `app/Models/Coupon.php`. Route `POST /api/v1/coupons/validate` already exists.

This task adds what's missing:
1. `GET /api/v1/coupons` — list active coupons for authenticated user
2. Admin CRUD: `GET/POST/PUT/DELETE /api/v1/admin/coupons`

## What Already Exists (do NOT recreate)

- `app/Modules/Campaign/Presentation/API/Controllers/CouponController.php` — add `index()` method
- `app/Modules/Campaign/Presentation/API/Resources/CouponResource.php` — already exists
- `app/Modules/Campaign/Domain/Contracts/CouponRepositoryInterface.php` — add `listActive()` method
- `app/Modules/Campaign/Infrastructure/Repositories/EloquentCouponRepository.php` — implement `listActive()`
- Routes in `routes/api.php` — add `GET /coupons` and admin routes

## Files to Create/Modify

| Action | Path |
|--------|------|
| Modify | `app/Modules/Campaign/Domain/Contracts/CouponRepositoryInterface.php` |
| Modify | `app/Modules/Campaign/Infrastructure/Repositories/EloquentCouponRepository.php` |
| Modify | `app/Modules/Campaign/Presentation/API/Controllers/CouponController.php` |
| Create | `app/Modules/Campaign/Presentation/API/Controllers/AdminCouponController.php` |
| Create | `app/Modules/Campaign/Presentation/API/Requests/StoreCouponRequest.php` |
| Create | `app/Modules/Campaign/Presentation/API/Requests/UpdateCouponRequest.php` |
| Modify | `routes/api.php` |
| Modify | `tests/Feature/CampaignApiTest.php` |

## GET /api/v1/coupons

**Controller method:** `CouponController::index(Request $request): JsonResponse`
**Auth:** sanctum required
**Filter:** `is_active = true AND (expires_at IS NULL OR expires_at > now()) AND (usage_limit IS NULL OR used_count < usage_limit)`

Response:
```json
{
  "coupons": [
    {
      "id": 1,
      "code": "FUSKA20",
      "type": "percentage",
      "discount_value": 20.0,
      "min_order_amount": 150.0,
      "max_discount_amount": 50.0,
      "expires_at": "2026-12-31T23:59:59Z",
      "description": "Tüm siparişlerde %20 indirim"
    }
  ]
}
```

Add `listActive(): Collection` to `CouponRepositoryInterface` and implement it in `EloquentCouponRepository`.

Check `Coupon` model fields — it may use `min_purchase_amount` instead of `min_order_amount`, `discount_amount` instead of `discount_value`. Use whatever fields exist on the model; map them in the resource. Also check for `description` field — add it to CouponResource if it exists, omit if not.

## Admin CRUD

**Controller:** `AdminCouponController` — requires `role:admin` middleware (already in admin route group).

Look at how existing admin routes are structured in `routes/api.php` (there's an admin group with `role:admin` middleware). Add the coupon admin routes to that group.

```
GET    /api/v1/admin/coupons        → index (paginated, all coupons)
POST   /api/v1/admin/coupons        → store
PUT    /api/v1/admin/coupons/{id}   → update
DELETE /api/v1/admin/coupons/{id}   → destroy (soft delete: set is_active=false)
```

**Store request fields** (all validated):
- `code` string required unique
- `type` in:percentage,fixed_amount required
- `discount_value` numeric min:0 required
- `min_order_amount` numeric min:0 nullable
- `max_discount_amount` numeric min:0 nullable
- `usage_limit` integer min:1 nullable
- `usage_limit_per_user` integer min:1 default:1
- `is_active` boolean default:true
- `starts_at` date nullable
- `expires_at` date nullable after:starts_at
- `description` string max:255 nullable

**Update request:** same fields but all optional.

**Destroy:** set `is_active = false` (soft deactivation, not physical delete).

## Tests

Add to `tests/Feature/CampaignApiTest.php`:

```php
test('customer can list active coupons', function () {
    $customer = \App\Models\User::factory()->create(['role' => 'customer', 'is_active' => true]);
    \App\Models\Coupon::factory()->create(['is_active' => true, 'expires_at' => now()->addDays(30)]);
    \App\Models\Coupon::factory()->create(['is_active' => false]);

    $this->actingAs($customer, 'sanctum')
        ->getJson('/api/v1/coupons')
        ->assertOk()
        ->assertJsonStructure(['coupons' => [['id', 'code', 'type', 'discount_value']]]);
});

test('admin can create a coupon', function () {
    $admin = \App\Models\User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/admin/coupons', [
            'code'           => 'TEST50',
            'type'           => 'fixed_amount',
            'discount_value' => 50.0,
            'is_active'      => true,
        ])
        ->assertCreated()
        ->assertJsonPath('data.code', 'TEST50');
});

test('admin can deactivate a coupon', function () {
    $admin  = \App\Models\User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $coupon = \App\Models\Coupon::factory()->create(['is_active' => true]);

    $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/v1/admin/coupons/{$coupon->id}")
        ->assertNoContent();

    $this->assertDatabaseHas('coupons', ['id' => $coupon->id, 'is_active' => false]);
});
```

If `Coupon::factory()` doesn't exist, create `database/factories/CouponFactory.php` with sensible defaults.

## Global Constraints

- Pest PHP syntax
- `php artisan test` must pass (333 baseline → target 336+)
- No new modules — extend existing Campaign module
- Follow existing controller patterns (return `response()->json(...)`)
- Admin routes go inside existing admin middleware group in `routes/api.php`
