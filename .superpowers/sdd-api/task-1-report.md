# Task 1 Implementation Report

## What Was Implemented

### Files Modified
1. **`CouponRepositoryInterface.php`** — Added `listActive(): Collection` method signature
2. **`EloquentCouponRepository.php`** — Implemented `listActive()` with filters: `is_active=true`, `start_date <= now()`, `end_date >= now()`, usage limit not exceeded
3. **`CouponController.php`** — Added `index()` method that calls `listActive()` and returns `{"coupons": [...]}` wrapper
4. **`CouponResource.php`** — Added `discount_value` as an alias for `value` so tests can check `discount_value` in JSON structure
5. **`routes/api.php`** — Added `GET /api/v1/coupons` to coupons group; added admin coupon CRUD routes (`/api/v1/admin/coupons`) with `role:admin` middleware
6. **`tests/Feature/CampaignApiTest.php`** — Added 3 new tests

### Files Created
1. **`AdminCouponController.php`** — index (paginated), store, update, destroy (soft-deactivate)
2. **`StoreCouponRequest.php`** — Validation for create (code required/unique, type, discount_value, etc.)
3. **`UpdateCouponRequest.php`** — Same fields but all optional with `sometimes`

## Deviations From Brief

### Field Name Mapping
The brief used names from the planned API spec that differ from actual DB columns. The mapping applied:
- `discount_value` (API) → `value` (DB) — added alias in CouponResource
- `min_order_amount` (API) → `min_purchase_amount` (DB)
- `starts_at` (API) → `start_date` (DB)
- `expires_at` (API) → `end_date` (DB)
- `description` — not on the model/migration; omitted from resource but accepted in requests (ignored silently)
- `usage_limit_per_user` — not on the model (`user_specific` boolean exists instead); accepted in request but not persisted

### Test Adaptations
- Brief's tests used `'expires_at' => now()->addDays(30)` in factory calls, but the real field is `end_date`. Tests adapted to use `end_date`.
- Brief's tests omitted `$admin->assignRole('admin')` which is required by Spatie Permission middleware. Added to both admin tests.
- The `listActive()` filter uses `start_date`/`end_date` columns (matching the model's `scopeActive`) rather than the brief's `expires_at IS NULL OR expires_at > now()` phrasing.

### listActive() Filter Logic
Brief: `is_active = true AND (expires_at IS NULL OR expires_at > now()) AND (usage_limit IS NULL OR used_count < usage_limit)`
Implemented: `is_active = true AND start_date <= now() AND end_date >= now() AND (usage_limit IS NULL OR usage_count < usage_limit)` — consistent with model's existing `scopeActive` plus usage limit check.

## Test Results

Command: `php artisan test`
Result: **336/336 passed** (up from 333 baseline, +3 new tests)
Duration: ~7.8s

Campaign-specific: `php artisan test --filter CampaignApiTest`
Result: **18/18 passed** (15 existing + 3 new)

## Self-Review Findings

- No logic errors found
- Soft-delete on coupon (set `is_active=false`) returns 204 No Content as required
- Admin store defaults `start_date=now()` and `end_date=now()->addYear()` when not provided — reasonable defaults for quick coupon creation
- `destroy()` uses `findOrFail` so returns 404 for non-existent coupon IDs
- `CouponFactory` already existed — no creation needed
