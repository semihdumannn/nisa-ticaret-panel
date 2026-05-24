<?php

namespace App\Modules\Notification\Infrastructure\Repositories;

use App\Models\FcmToken;
use App\Modules\Notification\Domain\Contracts\FcmTokenRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentFcmTokenRepository implements FcmTokenRepositoryInterface
{
    public function tokensForUser(int $userId): Collection
    {
        return FcmToken::where('user_id', $userId)->pluck('token');
    }

    public function register(int $userId, string $token, ?string $platform = null): void
    {
        FcmToken::updateOrCreate(
            ['user_id' => $userId, 'token' => $token],
            ['platform' => $platform],
        );
    }

    public function unregister(int $userId, string $token): void
    {
        FcmToken::where('user_id', $userId)->where('token', $token)->delete();
    }

    public function removeInvalid(array $tokens): void
    {
        if (! empty($tokens)) {
            FcmToken::whereIn('token', $tokens)->delete();
        }
    }
}
