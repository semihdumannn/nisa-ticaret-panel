<?php

namespace App\Modules\Notification\Domain\Contracts;

use Illuminate\Support\Collection;

interface FcmTokenRepositoryInterface
{
    /** Return all FCM token strings for a user. */
    public function tokensForUser(int $userId): Collection;

    /**
     * Register a token for a user.
     * Uses upsert — safe to call multiple times with the same token.
     */
    public function register(int $userId, string $token, ?string $platform = null): void;

    /** Remove a specific token. */
    public function unregister(int $userId, string $token): void;

    /** Remove invalid/stale tokens (called after FCM reports them as invalid). */
    public function removeInvalid(array $tokens): void;
}
