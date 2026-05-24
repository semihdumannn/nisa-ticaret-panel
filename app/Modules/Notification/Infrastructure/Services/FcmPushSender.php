<?php

namespace App\Modules\Notification\Infrastructure\Services;

use App\Modules\Notification\Domain\Contracts\PushSenderInterface;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Throwable;

class FcmPushSender implements PushSenderInterface
{
    public function __construct(private readonly Messaging $messaging) {}

    /**
     * Send a push notification to all provided FCM tokens.
     * Returns tokens that FCM reports as invalid so they can be pruned.
     *
     * @param  string[]  $tokens
     * @return string[]  Invalid tokens
     */
    public function send(array $tokens, string $title, string $body, array $data = []): array
    {
        if (empty($tokens)) {
            return [];
        }

        $notification = Notification::create($title, $body);
        $invalidTokens = [];

        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification($notification)
                    ->withData($data);

                $this->messaging->send($message);
            } catch (InvalidMessage $e) {
                $invalidTokens[] = $token;
            } catch (Throwable) {
                // Log but don't crash for transient errors
                logger()->warning('FCM send failed', ['token' => substr($token, 0, 20), 'error' => class_basename(Throwable::class)]);
            }
        }

        return $invalidTokens;
    }
}
