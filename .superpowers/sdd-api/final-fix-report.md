# Final Code Review Fix Report

## Fix 1 (Critical): IDOR in UpdateSubscriptionUseCase

**File:** `app/Modules/Subscription/Application/UseCases/UpdateSubscriptionUseCase.php`

Added ownership check for `address_id` before assigning it to `$updateData`. The check queries `Address::where('id', $data['address_id'])->where('user_id', $userId)->first()` and throws `SubscriptionException('Bu adres size ait değil.', 'ADDRESS_NOT_YOURS')` if the address doesn't belong to the current user.

## Fix 2: FAQ controllers — extend Controller and return JsonResponse

**Files:**
- `app/Modules/Faq/Presentation/API/Controllers/FaqController.php`
- `app/Modules/Faq/Presentation/API/Controllers/AdminFaqController.php`

Both controllers now extend `App\Http\Controllers\Controller`. `AdminFaqController`:
- `index()` now returns `response()->json(...)` with `JsonResponse` return type (was returning raw paginator)
- `store()` already returned `response()->json($item, 201)` — no change needed
- `update()` already returned `response()->json($item)` — no change needed
- `destroy()` now returns `response()->json(null, 204)` (was using `response()->noContent()`)
- Added `use Illuminate\Http\JsonResponse` and explicit `JsonResponse` return types

`FaqController::index()` already returned `response()->json(...)` — only added the `Controller` base class.

## Fix 3: Cross-user ownership tests for Reviews

**File:** `tests/Feature/ReviewApiTest.php`

Added two new tests:
1. `cannot submit review for another users order` — verifies 422 + `NOT_YOUR_ORDER` error code when user2 tries to submit a review for user1's order
2. `cannot get review status of another users order` — verifies 403 when user2 tries to get review status of user1's order

Both cases were already enforced in the use cases (`SubmitReviewUseCase` throws `ReviewNotAllowedException('NOT_YOUR_ORDER')`, `GetOrderReviewStatusUseCase` calls `abort_if()`). Tests confirm the behavior.

## Fix 4: PUT /subscriptions/{id} test

**File:** `tests/Feature/SubscriptionApiTest.php`

Added `user can update subscription plan` test that creates an active monthly subscription and PUTs `{'plan': 'weekly'}`, asserting `plan === 'weekly'` and `discount_rate === 10.0`.

Also fixed `SubscriptionController::update()` to pass `JSON_PRESERVE_ZERO_FRACTION` to `response()->json()` so that PHP serializes `10.0` as `10.0` (not `10`) in the JSON output, matching the strict float assertion in the test.

## Test Results

- Before fixes: 363 tests passing
- After fixes: **366 tests passing** (3 new tests added)
- All assertions: 890 passing, 0 failures
