<?php

namespace App\Modules\Notification\Infrastructure\Jobs;

use App\Modules\Notification\Domain\Contracts\FcmTokenRepositoryInterface;
use App\Modules\Notification\Domain\Contracts\PushSenderInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPushNotificationJob implements ShouldQueue
{
    use Queueable;

    /** Max attempts before moving to failed_jobs. */
    public int $tries = 3;

    /** Timeout per attempt in seconds. */
    public int $timeout = 30;

    /** Exponential backoff: 10s → 60s → 300s between retries. */
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function __construct(
        public readonly int    $userId,
        public readonly string $title,
        public readonly string $body,
        public readonly array  $data = [],
    ) {}

    public function handle(
        FcmTokenRepositoryInterface $tokenRepo,
        PushSenderInterface         $pushSender,
    ): void {
        $tokens = $tokenRepo->tokensForUser($this->userId)->all();

        if (empty($tokens)) {
            return;
        }

        $invalidTokens = $pushSender->send($tokens, $this->title, $this->body, $this->data);

        if (! empty($invalidTokens)) {
            $tokenRepo->removeInvalid($invalidTokens);
        }
    }
}
