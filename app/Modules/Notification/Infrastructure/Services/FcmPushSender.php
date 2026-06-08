<?php

namespace App\Modules\Notification\Infrastructure\Services;

use App\Modules\Notification\Domain\Contracts\PushSenderInterface;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\Messaging\AuthenticationError;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\MessagingException;
use Throwable;

class FcmPushSender implements PushSenderInterface
{
    public function __construct(private readonly Messaging $messaging) {}

    /**
     * @param  string[]  $tokens
     * @return string[]  Invalid (unregistered) tokens
     */
    public function send(array $tokens, string $title, string $body, array $data = []): array
    {
        if (empty($tokens)) {
            return [];
        }

        $notification  = Notification::create($title, $body);
        $invalidTokens = [];

        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::new()
                    ->withToken($token)
                    ->withNotification($notification)
                    ->withData($data);

                $this->messaging->send($message);
            } catch (NotFound | InvalidMessage $e) {
                $invalidTokens[] = $token;
            } catch (AuthenticationError $e) {
                logger()->error('FCM authentication failed — credentials invalid or revoked', [
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            } catch (MessagingException $e) {
                logger()->warning('FCM send failed', [
                    'token' => substr($token, 0, 20),
                    'class' => get_class($e),
                    'error' => $e->getMessage(),
                ]);
            } catch (Throwable $e) {
                logger()->warning('FCM unexpected error', [
                    'token' => substr($token, 0, 20),
                    'class' => get_class($e),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $invalidTokens;
    }
}
