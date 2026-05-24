<?php

namespace App\Modules\Notification\Application\UseCases;

use App\Modules\Notification\Domain\Contracts\FcmTokenRepositoryInterface;

class RegisterDeviceTokenUseCase
{
    public function __construct(
        private readonly FcmTokenRepositoryInterface $tokenRepo,
    ) {}

    public function execute(int $userId, string $token, ?string $platform = null): void
    {
        $this->tokenRepo->register($userId, $token, $platform);
    }
}
