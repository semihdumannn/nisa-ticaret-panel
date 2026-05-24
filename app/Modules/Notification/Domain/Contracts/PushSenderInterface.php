<?php

namespace App\Modules\Notification\Domain\Contracts;

interface PushSenderInterface
{
    /**
     * Send a push notification to multiple FCM tokens.
     *
     * Returns the list of tokens that are invalid / should be removed.
     *
     * @param  string[]  $tokens
     * @return string[]  Invalid tokens
     */
    public function send(array $tokens, string $title, string $body, array $data = []): array;
}
