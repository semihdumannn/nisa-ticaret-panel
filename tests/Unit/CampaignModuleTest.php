<?php

use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\User;
use App\Modules\Campaign\Application\DTOs\ApplyCouponDTO;
use App\Modules\Campaign\Application\UseCases\ValidateCouponUseCase;
use App\Modules\Campaign\Domain\Exceptions\CouponMinPurchaseException;
use App\Modules\Campaign\Domain\Exceptions\CouponUsageLimitException;
use App\Modules\Campaign\Domain\Exceptions\InvalidCouponException;
use App\Modules\Campaign\Domain\ValueObjects\CampaignType;
use App\Modules\Campaign\Domain\ValueObjects\CouponType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── CampaignType enum ─────────────────────────────────────────────────────────

test('CampaignType has correct values', function () {
    expect(CampaignType::PERCENTAGE->value)->toBe('percentage')
        ->and(CampaignType::FIXED_AMOUNT->value)->toBe('fixed_amount')
        ->and(CampaignType::BUY_X_GET_Y->value)->toBe('buy_x_get_y');
});

test('CampaignType labels are non-empty strings', function () {
    foreach (CampaignType::cases() as $type) {
        expect($type->label())->toBeString()->not->toBeEmpty();
    }
});

test('CampaignType colors are valid Filament color strings', function () {
    foreach (CampaignType::cases() as $type) {
        expect($type->color())->toBeString()->not->toBeEmpty();
    }
});

// ── CouponType enum ───────────────────────────────────────────────────────────

test('CouponType has correct values', function () {
    expect(CouponType::PERCENTAGE->value)->toBe('percentage')
        ->and(CouponType::FIXED_AMOUNT->value)->toBe('fixed_amount');
});

// ── Campaign model ────────────────────────────────────────────────────────────

test('campaign isCurrentlyActive returns true for active in-range campaign', function () {
    $campaign = Campaign::factory()->create([
        'is_active'  => true,
        'start_date' => now()->subHour(),
        'end_date'   => now()->addHour(),
    ]);
    expect($campaign->isCurrentlyActive())->toBeTrue();
});

test('campaign isCurrentlyActive returns false when is_active is false', function () {
    $campaign = Campaign::factory()->inactive()->create();
    expect($campaign->isCurrentlyActive())->toBeFalse();
});

test('campaign isCurrentlyActive returns false when not yet started', function () {
    $campaign = Campaign::factory()->upcoming()->create();
    expect($campaign->isCurrentlyActive())->toBeFalse();
});

test('campaign isCurrentlyActive returns false when expired', function () {
    $campaign = Campaign::factory()->expired()->create();
    expect($campaign->isCurrentlyActive())->toBeFalse();
});

test('campaign isUsageLimitReached returns false when no limit set', function () {
    $campaign = Campaign::factory()->create(['usage_limit' => null]);
    expect($campaign->isUsageLimitReached())->toBeFalse();
});

test('campaign isUsageLimitReached returns true when count meets limit', function () {
    $campaign = Campaign::factory()->usageLimitReached()->create();
    expect($campaign->isUsageLimitReached())->toBeTrue();
});

test('campaign calculateDiscount computes percentage correctly', function () {
    $campaign = Campaign::factory()->percentage(20)->create();
    expect($campaign->calculateDiscount(100.0))->toBe(20.0);
});

test('campaign calculateDiscount caps at max_discount_amount', function () {
    $campaign = Campaign::factory()->percentage(50)->create(['max_discount_amount' => 30]);
    // 50% of 200 = 100, capped at 30
    expect($campaign->calculateDiscount(200.0))->toBe(30.0);
});

test('campaign calculateDiscount computes fixed amount correctly', function () {
    $campaign = Campaign::factory()->fixedAmount(15)->create();
    expect($campaign->calculateDiscount(100.0))->toBe(15.0);
});

test('campaign calculateDiscount never exceeds subtotal', function () {
    $campaign = Campaign::factory()->fixedAmount(500)->create();
    expect($campaign->calculateDiscount(50.0))->toBe(50.0);
});

test('campaign calculateDiscount returns 0 for buy_x_get_y type', function () {
    $campaign = Campaign::factory()->create([
        'type'  => CampaignType::BUY_X_GET_Y->value,
        'value' => 1,
    ]);
    expect($campaign->calculateDiscount(100.0))->toBe(0.0);
});

test('campaign calculateDiscount returns 0 when min_purchase_amount not met', function () {
    $campaign = Campaign::factory()->percentage(10)->create(['min_purchase_amount' => 200]);
    expect($campaign->calculateDiscount(100.0))->toBe(0.0);
});

test('campaign calculateDiscount returns 0 when not currently active', function () {
    $campaign = Campaign::factory()->expired()->create();
    expect($campaign->calculateDiscount(100.0))->toBe(0.0);
});

test('campaign scopeActive excludes inactive campaigns', function () {
    Campaign::factory()->create(); // active
    Campaign::factory()->inactive()->create();
    Campaign::factory()->expired()->create();
    Campaign::factory()->upcoming()->create();

    $active = Campaign::active()->get();
    expect($active)->toHaveCount(1);
});

// ── Coupon model ──────────────────────────────────────────────────────────────

test('coupon isCurrentlyActive returns true for active in-range coupon', function () {
    $coupon = Coupon::factory()->create();
    expect($coupon->isCurrentlyActive())->toBeTrue();
});

test('coupon isCurrentlyActive returns false for expired coupon', function () {
    $coupon = Coupon::factory()->expired()->create();
    expect($coupon->isCurrentlyActive())->toBeFalse();
});

test('coupon calculateDiscount computes percentage correctly', function () {
    $coupon = Coupon::factory()->percentage(10)->create();
    expect($coupon->calculateDiscount(100.0))->toBe(10.0);
});

test('coupon calculateDiscount computes fixed amount correctly', function () {
    $coupon = Coupon::factory()->fixedAmount(25)->create();
    expect($coupon->calculateDiscount(100.0))->toBe(25.0);
});

test('coupon calculateDiscount caps at max_discount_amount', function () {
    $coupon = Coupon::factory()->percentage(50)->create(['max_discount_amount' => 20]);
    expect($coupon->calculateDiscount(200.0))->toBe(20.0);
});

test('coupon calculateDiscount never exceeds subtotal', function () {
    $coupon = Coupon::factory()->fixedAmount(999)->create();
    expect($coupon->calculateDiscount(50.0))->toBe(50.0);
});

// ── ValidateCouponUseCase ─────────────────────────────────────────────────────

test('ValidateCouponUseCase returns coupon for valid code', function () {
    $coupon = Coupon::factory()->percentage(15, 'VALID15')->create();
    $user   = User::factory()->create();

    $useCase = app(ValidateCouponUseCase::class);
    $result  = $useCase->execute(new ApplyCouponDTO(
        code:     'VALID15',
        userId:   $user->id,
        subtotal: 100.0,
    ));

    expect($result->id)->toBe($coupon->id);
});

test('ValidateCouponUseCase throws for unknown code', function () {
    $user = User::factory()->create();
    expect(fn () => app(ValidateCouponUseCase::class)->execute(new ApplyCouponDTO(
        code:     'BADCODE',
        userId:   $user->id,
        subtotal: 100.0,
    )))->toThrow(InvalidCouponException::class);
});

test('ValidateCouponUseCase throws for expired coupon', function () {
    Coupon::factory()->expired()->create(['code' => 'EXPIRED']);
    $user = User::factory()->create();

    expect(fn () => app(ValidateCouponUseCase::class)->execute(new ApplyCouponDTO(
        code:     'EXPIRED',
        userId:   $user->id,
        subtotal: 100.0,
    )))->toThrow(InvalidCouponException::class);
});

test('ValidateCouponUseCase throws when usage limit reached', function () {
    Coupon::factory()->usageLimitReached()->create(['code' => 'MAXED']);
    $user = User::factory()->create();

    expect(fn () => app(ValidateCouponUseCase::class)->execute(new ApplyCouponDTO(
        code:     'MAXED',
        userId:   $user->id,
        subtotal: 100.0,
    )))->toThrow(CouponUsageLimitException::class);
});

test('ValidateCouponUseCase throws when min purchase not met', function () {
    Coupon::factory()->withMinPurchase(200)->create(['code' => 'MINBUY']);
    $user = User::factory()->create();

    expect(fn () => app(ValidateCouponUseCase::class)->execute(new ApplyCouponDTO(
        code:     'MINBUY',
        userId:   $user->id,
        subtotal: 100.0,
    )))->toThrow(CouponMinPurchaseException::class);
});

test('ValidateCouponUseCase throws when user_specific coupon already used', function () {
    $coupon = Coupon::factory()->userSpecific()->create(['code' => 'ONCE']);
    $user   = User::factory()->create();

    // Simulate prior usage
    $order = \App\Models\Order::factory()->create(['customer_id' => $user->id]);
    CouponUsage::create(['coupon_id' => $coupon->id, 'user_id' => $user->id, 'order_id' => $order->id]);

    expect(fn () => app(ValidateCouponUseCase::class)->execute(new ApplyCouponDTO(
        code:     'ONCE',
        userId:   $user->id,
        subtotal: 100.0,
    )))->toThrow(InvalidCouponException::class);
});
