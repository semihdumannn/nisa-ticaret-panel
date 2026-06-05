<?php

namespace App\Providers;

use App\Modules\Notification\Domain\Contracts\FcmTokenRepositoryInterface;
use App\Modules\Notification\Domain\Contracts\NotificationRepositoryInterface;
use App\Modules\Notification\Domain\Contracts\PushSenderInterface;
use App\Modules\Notification\Domain\Events\OrderCancelledEvent;
use App\Modules\Notification\Domain\Events\OrderPlacedEvent;
use App\Modules\Notification\Domain\Events\OrderStatusUpdatedEvent;
use App\Modules\Notification\Infrastructure\Listeners\AdminOrderNotificationListener;
use App\Modules\Notification\Infrastructure\Listeners\OrderNotificationListener;
use App\Modules\Notification\Infrastructure\Repositories\EloquentFcmTokenRepository;
use App\Modules\Notification\Infrastructure\Repositories\EloquentNotificationRepository;
use App\Modules\Notification\Infrastructure\Services\FcmPushSender;
use App\Modules\Notification\Infrastructure\Services\NullPushSender;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class NotificationModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NotificationRepositoryInterface::class, EloquentNotificationRepository::class);
        $this->app->bind(FcmTokenRepositoryInterface::class, EloquentFcmTokenRepository::class);

        // Use NullPushSender when Firebase credentials file is not present (tests / local dev).
        // Handle both absolute paths (set by Docker entrypoint) and relative paths.
        $credentialsPath = config('firebase.projects.' . config('firebase.default') . '.credentials', '');
        $resolvedPath    = $credentialsPath && str_starts_with($credentialsPath, '/')
            ? $credentialsPath
            : ($credentialsPath ? base_path($credentialsPath) : '');
        $hasCredentials  = $resolvedPath && file_exists($resolvedPath);

        if ($hasCredentials) {
            $this->app->bind(PushSenderInterface::class, FcmPushSender::class);
        } else {
            $this->app->bind(PushSenderInterface::class, NullPushSender::class);
        }
    }

    public function boot(): void
    {
        // ── Customer push notifications ───────────────────────────────────────
        Event::listen(OrderPlacedEvent::class, [OrderNotificationListener::class, 'handleOrderPlaced']);
        Event::listen(OrderStatusUpdatedEvent::class, [OrderNotificationListener::class, 'handleOrderStatusUpdated']);
        Event::listen(OrderCancelledEvent::class, [OrderNotificationListener::class, 'handleOrderCancelled']);

        // ── Admin Filament database notifications ─────────────────────────────
        Event::listen(OrderPlacedEvent::class, [AdminOrderNotificationListener::class, 'handleOrderPlaced']);
        Event::listen(OrderStatusUpdatedEvent::class, [AdminOrderNotificationListener::class, 'handleOrderStatusUpdated']);
    }
}
