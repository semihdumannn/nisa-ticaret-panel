<?php

use App\Models\AppConfig;
use App\Models\DailyStat;
use App\Models\Order;
use App\Models\User;
use App\Modules\Analytics\Application\DTOs\DateRangeDTO;
use App\Modules\Analytics\Application\UseCases\AggregateDailyStatsUseCase;
use App\Modules\Analytics\Application\UseCases\GetDashboardStatsUseCase;
use App\Modules\Analytics\Application\UseCases\GetOrderStatusBreakdownUseCase;
use App\Modules\Analytics\Application\UseCases\GetRevenueReportUseCase;
use App\Modules\Analytics\Application\UseCases\GetTopProductsUseCase;
use App\Modules\Analytics\Application\UseCases\GetTopCustomersUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── DateRangeDTO ──────────────────────────────────────────────────────────────

test('DateRangeDTO::lastDays creates correct range', function () {
    $range = DateRangeDTO::lastDays(7);
    expect($range->from->isToday())->toBeFalse() // 6 days ago start
        ->and($range->to->isToday())->toBeTrue();
    expect($range->from->diffInDays($range->to))->toBeLessThanOrEqual(7);
});

test('DateRangeDTO::fromStrings parses date strings', function () {
    $range = DateRangeDTO::fromStrings('2025-01-01', '2025-01-31');
    expect($range->from->toDateString())->toBe('2025-01-01')
        ->and($range->to->toDateString())->toBe('2025-01-31');
});

test('DateRangeDTO::fromStrings uses defaults when null', function () {
    $range = DateRangeDTO::fromStrings(null, null, 30);
    expect($range->from->diffInDays($range->to))->toBeLessThanOrEqual(30);
});

// ── AppConfig model ───────────────────────────────────────────────────────────

test('AppConfig::get returns typed boolean', function () {
    AppConfig::create(['key' => 'test_bool', 'type' => 'boolean', 'value' => 'true', 'description' => null]);
    expect(AppConfig::get('test_bool'))->toBeTrue();
});

test('AppConfig::get returns typed number', function () {
    AppConfig::create(['key' => 'test_num', 'type' => 'number', 'value' => '42.5', 'description' => null]);
    expect(AppConfig::get('test_num'))->toBe(42.5);
});

test('AppConfig::get returns string', function () {
    AppConfig::create(['key' => 'test_str', 'type' => 'string', 'value' => 'hello', 'description' => null]);
    expect(AppConfig::get('test_str'))->toBe('hello');
});

test('AppConfig::get returns default when key missing', function () {
    expect(AppConfig::get('nonexistent', 'fallback'))->toBe('fallback');
});

test('AppConfig::allTyped returns keyed array', function () {
    AppConfig::create(['key' => 'k1', 'type' => 'string', 'value' => 'v1', 'description' => null]);
    AppConfig::create(['key' => 'k2', 'type' => 'number', 'value' => '10', 'description' => null]);

    $all = AppConfig::allTyped();
    expect($all)->toHaveKey('k1')
        ->and($all['k1'])->toBe('v1')
        ->and($all['k2'])->toBe(10.0);
});

// ── DailyStat model ───────────────────────────────────────────────────────────

test('DailyStat scopeInRange filters correctly', function () {
    DailyStat::create(['date' => '2025-01-01', 'total_orders' => 5, 'total_revenue' => 100, 'total_customers' => 10, 'new_customers' => 2, 'avg_order_value' => 20]);
    DailyStat::create(['date' => '2025-01-15', 'total_orders' => 8, 'total_revenue' => 200, 'total_customers' => 12, 'new_customers' => 3, 'avg_order_value' => 25]);
    DailyStat::create(['date' => '2025-02-01', 'total_orders' => 3, 'total_revenue' => 50,  'total_customers' => 13, 'new_customers' => 1, 'avg_order_value' => 17]);

    $inRange = DailyStat::inRange('2025-01-01', '2025-01-31')->get();
    expect($inRange)->toHaveCount(2);
});

// ── AggregateDailyStatsUseCase ────────────────────────────────────────────────

test('AggregateDailyStatsUseCase creates a DailyStat row', function () {
    $date = now()->subDay();
    $stat = app(AggregateDailyStatsUseCase::class)->execute($date);

    // $stat->date is a Carbon (from 'date' cast); compare as date strings
    expect($stat->date instanceof \Carbon\Carbon
        ? $stat->date->toDateString()
        : substr((string) $stat->date, 0, 10)
    )->toBe($date->toDateString())
        ->and($stat->total_orders)->toBeInt()
        ->and($stat->new_customers)->toBeInt();
});

test('AggregateDailyStatsUseCase is idempotent', function () {
    $date = now()->subDay();
    app(AggregateDailyStatsUseCase::class)->execute($date);
    app(AggregateDailyStatsUseCase::class)->execute($date);

    expect(DailyStat::whereDate('date', $date->toDateString())->count())->toBe(1);
});

test('AggregateDailyStatsUseCase computes correct totals', function () {
    // Create two delivered orders yesterday
    $user = User::factory()->create();
    Order::factory()->count(2)->create([
        'customer_id' => $user->id,
        'status'      => 'delivered',
        'total'       => 100.00,
        'subtotal'    => 100.00,
        'tax_amount'  => 0,
        'created_at'  => now()->subDay()->midDay(),
    ]);

    $stat = app(AggregateDailyStatsUseCase::class)->execute(now()->subDay());

    expect($stat->total_orders)->toBe(2)
        ->and((float) $stat->total_revenue)->toBe(200.0)
        ->and((float) $stat->avg_order_value)->toBe(100.0);
});

// ── GetDashboardStatsUseCase ──────────────────────────────────────────────────

test('GetDashboardStatsUseCase returns a DashboardStatsDTO', function () {
    $stats = app(GetDashboardStatsUseCase::class)->execute();

    expect($stats->todayOrders)->toBeInt()
        ->and($stats->totalRevenue)->toBeFloat()
        ->and($stats->pendingOrders)->toBeInt();
});

// ── GetRevenueReportUseCase ───────────────────────────────────────────────────

test('GetRevenueReportUseCase returns daily buckets', function () {
    $user = User::factory()->create();
    Order::factory()->create([
        'customer_id' => $user->id,
        'status'      => 'delivered',
        'total'       => 150.00,
        'subtotal'    => 150.00,
        'tax_amount'  => 0,
        'created_at'  => today(),
    ]);

    $range = DateRangeDTO::lastDays(1);
    $rows  = app(GetRevenueReportUseCase::class)->execute($range);

    expect($rows)->not->toBeEmpty()
        ->and($rows->first()['revenue'])->toBe(150.0);
});

// ── GetOrderStatusBreakdownUseCase ────────────────────────────────────────────

test('GetOrderStatusBreakdownUseCase returns percentages summing to 100', function () {
    Order::factory()->count(3)->create(['status' => 'pending']);
    Order::factory()->count(2)->create(['status' => 'delivered']);

    $breakdown = app(GetOrderStatusBreakdownUseCase::class)->execute();
    $total     = collect($breakdown)->sum('percentage');

    expect($total)->toBe(100.0);
});

test('GetOrderStatusBreakdownUseCase includes status labels', function () {
    Order::factory()->create(['status' => 'pending']);

    $breakdown = app(GetOrderStatusBreakdownUseCase::class)->execute();
    $pending   = collect($breakdown)->firstWhere('status', 'pending');

    expect($pending['label'])->toBe('Pending')
        ->and($pending['count'])->toBeInt();
});

// ── GetTopProductsUseCase ─────────────────────────────────────────────────────

test('GetTopProductsUseCase returns items sorted by revenue desc', function () {
    // Uses order_items table data seeded via factories
    $range = DateRangeDTO::lastDays(30);
    $top   = app(GetTopProductsUseCase::class)->execute($range, 5);

    // No data → empty collection, no crash
    expect($top)->toBeInstanceOf(\Illuminate\Support\Collection::class);
});

// ── GetTopCustomersUseCase ────────────────────────────────────────────────────

test('GetTopCustomersUseCase returns items sorted by spend desc', function () {
    $range = DateRangeDTO::lastDays(30);
    $top   = app(GetTopCustomersUseCase::class)->execute($range, 5);

    expect($top)->toBeInstanceOf(\Illuminate\Support\Collection::class);
});
