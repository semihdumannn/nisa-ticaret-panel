<?php

namespace App\Modules\Notification\Infrastructure\Services;

use App\Modules\Notification\Domain\Contracts\PushSenderInterface;

/**
 * No-op push sender used when Firebase credentials are not configured.
 * Used in testing and development environments without real FCM credentials.
 */
class NullPushSender implements PushSenderInterface
{
    public function send(array $tokens, string $title, string $body, array $data = []): array
    {
        // Intentionally a no-op — returns empty invalid-token list.
        return [];
    }
}
