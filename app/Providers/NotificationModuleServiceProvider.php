<?php

namespace App\Providers;

use App\Modules\Notification\Domain\Contracts\FcmTokenRepositoryInterface;
use App\Modules\Notification\Domain\Contracts\NotificationRepositoryInterface;
use App\Modules\Notification\Domain\Contracts\PushSenderInterface;
use App\Modules\Notification\Domain\Events\OrderCancelledEvent;
use App\Modules\Notification\Domain\Events\OrderPlacedEvent;
use App\Modules\Notification\Domain\Events\OrderStatusUpdatedEvent;
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
        $credentialsPath = config('firebase.projects.' . config('firebase.default') . '.credentials', '');
        $hasCredentials  = $credentialsPath && file_exists(base_path($credentialsPath));

        if ($hasCredentials) {
            $this->app->bind(PushSenderInterface::class, FcmPushSender::class);
        } else {
            $this->app->bind(PushSenderInterface::class, NullPushSender::class);
        }
    }

    public function boot(): void
    {
        Event::listen(OrderPlacedEvent::class, [OrderNotificationListener::class, 'handleOrderPlaced']);
        Event::listen(OrderStatusUpdatedEvent::class, [OrderNotificationListener::class, 'handleOrderStatusUpdated']);
        Event::listen(OrderCancelledEvent::class, [OrderNotificationListener::class, 'handleOrderCancelled']);
    }
}
