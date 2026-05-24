<?php

use App\Models\FcmToken;
use App\Models\Notification;
use App\Models\User;
use App\Modules\Notification\Application\DTOs\SendNotificationDTO;
use App\Modules\Notification\Application\UseCases\MarkNotificationsReadUseCase;
use App\Modules\Notification\Application\UseCases\RegisterDeviceTokenUseCase;
use App\Modules\Notification\Application\UseCases\SendNotificationUseCase;
use App\Modules\Notification\Domain\ValueObjects\NotificationType;
use App\Modules\Notification\Infrastructure\Services\NullPushSender;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── NotificationType enum ─────────────────────────────────────────────────────

test('NotificationType has correct values', function () {
    expect(NotificationType::ORDER_UPDATE->value)->toBe('order_update')
        ->and(NotificationType::PROMOTION->value)->toBe('promotion')
        ->and(NotificationType::SYSTEM->value)->toBe('system');
});

test('NotificationType labels are non-empty strings', function () {
    foreach (NotificationType::cases() as $type) {
        expect($type->label())->toBeString()->not->toBeEmpty();
    }
});

test('NotificationType colors are non-empty strings', function () {
    foreach (NotificationType::cases() as $type) {
        expect($type->color())->toBeString()->not->toBeEmpty();
    }
});

// ── Notification model ────────────────────────────────────────────────────────

test('Notification model has correct casts', function () {
    $user         = User::factory()->create();
    $notification = Notification::factory()->create([
        'user_id' => $user->id,
        'data'    => ['order_id' => 1],
    ]);

    expect($notification->data)->toBeArray()
        ->and($notification->is_read)->toBeFalse();
});

test('Notification markAsRead sets is_read and read_at', function () {
    $notification = Notification::factory()->unread()->create();
    $notification->markAsRead();

    expect($notification->fresh()->is_read)->toBeTrue()
        ->and($notification->fresh()->read_at)->not->toBeNull();
});

test('Notification markAsRead is idempotent', function () {
    $notification = Notification::factory()->read()->create();
    $readAt       = $notification->read_at;

    $notification->markAsRead();

    // read_at should not change
    expect($notification->fresh()->read_at->toDateTimeString())
        ->toBe($readAt->toDateTimeString());
});

test('Notification scopeUnread filters correctly', function () {
    $user = User::factory()->create();
    Notification::factory()->count(3)->unread()->create(['user_id' => $user->id]);
    Notification::factory()->count(2)->read()->create(['user_id' => $user->id]);

    $unread = Notification::forUser($user->id)->unread()->get();
    expect($unread)->toHaveCount(3);
});

// ── NullPushSender ────────────────────────────────────────────────────────────

test('NullPushSender returns empty invalid tokens list', function () {
    $sender  = new NullPushSender();
    $invalid = $sender->send(['token1', 'token2'], 'Test', 'Body', []);
    expect($invalid)->toBeArray()->toBeEmpty();
});

// ── SendNotificationUseCase ───────────────────────────────────────────────────

test('SendNotificationUseCase persists notification to DB', function () {
    $user = User::factory()->create();

    $useCase = app(SendNotificationUseCase::class);
    $result  = $useCase->execute(new SendNotificationDTO(
        userId: $user->id,
        type:   NotificationType::ORDER_UPDATE,
        title:  'Test Title',
        body:   'Test body.',
        data:   ['order_id' => 42],
    ));

    expect($result->id)->toBeInt();
    $this->assertDatabaseHas('notifications', [
        'user_id' => $user->id,
        'type'    => 'order_update',
        'title'   => 'Test Title',
        'is_read' => false,
    ]);
});

// ── MarkNotificationsReadUseCase ──────────────────────────────────────────────

test('MarkNotificationsReadUseCase marks all notifications as read', function () {
    $user = User::factory()->create();
    Notification::factory()->count(4)->unread()->create(['user_id' => $user->id]);

    app(MarkNotificationsReadUseCase::class)->execute($user->id);

    expect(Notification::where('user_id', $user->id)->where('is_read', false)->count())->toBe(0);
});

test('MarkNotificationsReadUseCase marks specific IDs as read', function () {
    $user = User::factory()->create();
    $n1   = Notification::factory()->unread()->create(['user_id' => $user->id]);
    $n2   = Notification::factory()->unread()->create(['user_id' => $user->id]);

    app(MarkNotificationsReadUseCase::class)->execute($user->id, [$n1->id]);

    expect($n1->fresh()->is_read)->toBeTrue()
        ->and($n2->fresh()->is_read)->toBeFalse();
});

test('MarkNotificationsReadUseCase cannot mark another users notifications', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $n     = Notification::factory()->unread()->create(['user_id' => $user2->id]);

    app(MarkNotificationsReadUseCase::class)->execute($user1->id, [$n->id]);

    expect($n->fresh()->is_read)->toBeFalse(); // not affected
});

// ── RegisterDeviceTokenUseCase ────────────────────────────────────────────────

test('RegisterDeviceTokenUseCase creates a new FCM token', function () {
    $user = User::factory()->create();

    app(RegisterDeviceTokenUseCase::class)->execute($user->id, 'test-token-abc', 'android');

    $this->assertDatabaseHas('fcm_tokens', [
        'user_id'  => $user->id,
        'token'    => 'test-token-abc',
        'platform' => 'android',
    ]);
});

test('RegisterDeviceTokenUseCase is idempotent for duplicate tokens', function () {
    $user = User::factory()->create();

    app(RegisterDeviceTokenUseCase::class)->execute($user->id, 'dup-token', 'ios');
    app(RegisterDeviceTokenUseCase::class)->execute($user->id, 'dup-token', 'ios');

    expect(FcmToken::where('user_id', $user->id)->where('token', 'dup-token')->count())->toBe(1);
});
