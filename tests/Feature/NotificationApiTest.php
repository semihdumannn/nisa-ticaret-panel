<?php

use App\Models\Address;
use App\Models\FcmToken;
use App\Models\Inventory;
use App\Models\Notification;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Modules\Notification\Domain\Events\OrderPlacedEvent;
use App\Modules\Notification\Domain\Events\OrderStatusUpdatedEvent;
use App\Modules\Notification\Domain\ValueObjects\NotificationType;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// ── Helpers ───────────────────────────────────────────────────────────────────

function notifUser(string $phone = '+905550009001'): array
{
    $user  = User::factory()->create(['phone' => $phone]);
    $token = $user->createToken('test')->plainTextToken;
    return [$user, $token];
}

function notifAdmin(string $phone = '+905550009000'): array
{
    $user = User::factory()->admin()->create(['phone' => $phone]);
    $user->assignRole('admin');
    $token = $user->createToken('test')->plainTextToken;
    return [$user, $token];
}

// ── GET /api/v1/notifications ─────────────────────────────────────────────────

test('authenticated user can list their notifications', function () {
    [$user, $token] = notifUser('+905550009002');
    Notification::factory()->count(5)->create(['user_id' => $user->id]);
    Notification::factory()->count(3)->create(); // other users

    $response = $this->withToken($token)
        ->getJson('/api/v1/notifications')
        ->assertStatus(200);

    expect($response->json('data'))->toHaveCount(5);
});

test('notifications are paginated', function () {
    [$user, $token] = notifUser('+905550009003');
    Notification::factory()->count(25)->create(['user_id' => $user->id]);

    $response = $this->withToken($token)
        ->getJson('/api/v1/notifications')
        ->assertStatus(200);

    // Default per-page is 20
    expect($response->json('data'))->toHaveCount(20)
        ->and($response->json('meta.total'))->toBe(25);
});

test('unauthenticated request to notifications is rejected', function () {
    $this->getJson('/api/v1/notifications')->assertStatus(401);
});

// ── GET /api/v1/notifications/unread-count ─────────────────────────────────────

test('unread count returns correct number', function () {
    [$user, $token] = notifUser('+905550009004');
    Notification::factory()->count(3)->unread()->create(['user_id' => $user->id]);
    Notification::factory()->count(2)->read()->create(['user_id' => $user->id]);

    $response = $this->withToken($token)
        ->getJson('/api/v1/notifications/unread-count')
        ->assertStatus(200);

    expect($response->json('unread_count'))->toBe(3);
});

// ── POST /api/v1/notifications/mark-read ──────────────────────────────────────

test('user can mark all notifications as read', function () {
    [$user, $token] = notifUser('+905550009005');
    Notification::factory()->count(4)->unread()->create(['user_id' => $user->id]);

    $response = $this->withToken($token)
        ->postJson('/api/v1/notifications/mark-read')
        ->assertStatus(200);

    expect($response->json('unread_count'))->toBe(0);
    expect(Notification::where('user_id', $user->id)->where('is_read', false)->count())->toBe(0);
});

test('user can mark specific notifications as read', function () {
    [$user, $token] = notifUser('+905550009006');
    $n1 = Notification::factory()->unread()->create(['user_id' => $user->id]);
    $n2 = Notification::factory()->unread()->create(['user_id' => $user->id]);

    $this->withToken($token)
        ->postJson('/api/v1/notifications/mark-read', ['ids' => [$n1->id]])
        ->assertStatus(200);

    expect($n1->fresh()->is_read)->toBeTrue()
        ->and($n2->fresh()->is_read)->toBeFalse();
});

// ── DELETE /api/v1/notifications/{id} ─────────────────────────────────────────

test('user can delete their own notification', function () {
    [$user, $token] = notifUser('+905550009007');
    $n = Notification::factory()->create(['user_id' => $user->id]);

    $this->withToken($token)
        ->deleteJson("/api/v1/notifications/{$n->id}")
        ->assertStatus(200);

    $this->assertDatabaseMissing('notifications', ['id' => $n->id]);
});

test('user cannot delete another users notification', function () {
    [$user1, $token1] = notifUser('+905550009008');
    $user2            = User::factory()->create(['phone' => '+905550009009']);
    $n                = Notification::factory()->create(['user_id' => $user2->id]);

    $this->withToken($token1)
        ->deleteJson("/api/v1/notifications/{$n->id}")
        ->assertStatus(404);
});

// ── POST /api/v1/devices ──────────────────────────────────────────────────────

test('user can register a device token', function () {
    [$user, $token] = notifUser('+905550009010');

    $this->withToken($token)
        ->postJson('/api/v1/devices', [
            'token'    => 'fcm-device-token-xyz',
            'platform' => 'android',
        ])
        ->assertStatus(201);

    $this->assertDatabaseHas('fcm_tokens', [
        'user_id'  => $user->id,
        'token'    => 'fcm-device-token-xyz',
        'platform' => 'android',
    ]);
});

test('device registration is idempotent', function () {
    [$user, $token] = notifUser('+905550009011');

    $this->withToken($token)->postJson('/api/v1/devices', ['token' => 'same-token', 'platform' => 'ios']);
    $this->withToken($token)->postJson('/api/v1/devices', ['token' => 'same-token', 'platform' => 'ios']);

    expect(FcmToken::where('user_id', $user->id)->count())->toBe(1);
});

test('device registration validates required token', function () {
    [, $token] = notifUser('+905550009012');

    $this->withToken($token)
        ->postJson('/api/v1/devices', ['platform' => 'ios'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['token']);
});

// ── DELETE /api/v1/devices ────────────────────────────────────────────────────

test('user can unregister a device token', function () {
    [$user, $token] = notifUser('+905550009013');
    FcmToken::factory()->create(['user_id' => $user->id, 'token' => 'remove-me']);

    $this->withToken($token)
        ->deleteJson('/api/v1/devices', ['token' => 'remove-me'])
        ->assertStatus(200);

    $this->assertDatabaseMissing('fcm_tokens', ['user_id' => $user->id, 'token' => 'remove-me']);
});

// ── Order event → notification integration ────────────────────────────────────

test('placing an order dispatches OrderPlacedEvent and creates notification', function () {
    [$user, $authToken] = notifUser('+905550009020');
    $product            = Product::factory()->create(['price' => 50, 'tax_rate' => 0]);
    $wh                 = Warehouse::factory()->create();
    $address            = Address::create(['user_id' => $user->id, 'full_address' => 'x', 'city' => 'Y']);

    Inventory::create([
        'product_id'         => $product->id,
        'warehouse_id'       => $wh->id,
        'quantity'           => 20,
        'reserved_quantity'  => 0,
    ]);

    // Fill cart
    app(\App\Modules\Order\Domain\Contracts\CartRepositoryInterface::class)
        ->addItem(
            app(\App\Modules\Order\Domain\Contracts\CartRepositoryInterface::class)->getOrCreate($user->id),
            $product->id, null, 1,
        );

    $this->withToken($authToken)
        ->postJson('/api/v1/orders', ['address_id' => $address->id])
        ->assertStatus(201);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $user->id,
        'type'    => NotificationType::ORDER_UPDATE->value,
    ]);
});

test('updating order status creates a notification', function () {
    $adminUser   = User::factory()->admin()->create(['phone' => '+905550009021']);
    $adminUser->assignRole('admin');
    $adminToken  = $adminUser->createToken('test')->plainTextToken;

    [$customer, ] = notifUser('+905550009022');
    $order = \App\Models\Order::factory()->create([
        'customer_id' => $customer->id,
        'status'      => 'pending',
    ]);

    $this->withToken($adminToken)
        ->putJson("/api/v1/admin/orders/{$order->id}/status", ['status' => 'confirmed'])
        ->assertStatus(200);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $customer->id,
        'type'    => NotificationType::ORDER_UPDATE->value,
    ]);
});

test('cancelling an order creates a notification', function () {
    [$user, $authToken] = notifUser('+905550009023');
    $order = \App\Models\Order::factory()->create([
        'customer_id' => $user->id,
        'status'      => 'pending',
    ]);

    $this->withToken($authToken)
        ->postJson("/api/v1/orders/{$order->id}/cancel")
        ->assertStatus(200);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $user->id,
        'type'    => NotificationType::ORDER_UPDATE->value,
    ]);
});

test('notification resource includes type_label', function () {
    [$user, $token] = notifUser('+905550009024');
    Notification::factory()->ofType(NotificationType::PROMOTION)->create(['user_id' => $user->id]);

    $response = $this->withToken($token)
        ->getJson('/api/v1/notifications')
        ->assertStatus(200);

    expect($response->json('data.0.type_label'))->toBe('Promotion');
});
