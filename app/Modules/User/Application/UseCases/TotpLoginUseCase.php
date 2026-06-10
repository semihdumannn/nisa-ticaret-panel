<?php

namespace App\Modules\User\Application\UseCases;

use App\Models\User;
use App\Modules\User\Application\DTOs\TotpLoginDTO;
use App\Modules\User\Domain\Contracts\TotpServiceInterface;
use App\Modules\User\Domain\Contracts\UserRepositoryInterface;
use App\Modules\User\Domain\Exceptions\InvalidTotpException;

class TotpLoginUseCase
{
    public function __construct(
        private readonly TotpServiceInterface $totp,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Verify TOTP code for a phone, return Sanctum token.
     *
     * @return array{user: User, token: string}
     */
    public function execute(TotpLoginDTO $dto): array
    {
        $user = $this->userRepository->findByPhone($dto->phone);

        if (! $user || ! $user->totp_secret) {
            throw new InvalidTotpException();
        }

        if (! $this->totp->verify($user->totp_secret, $dto->code, 10)) {
            throw new InvalidTotpException();
        }

        $this->userRepository->update($user, ['last_login_at' => now()]);
        $user->refresh();

        $deviceName = $dto->deviceName ?? 'mobile';
        $token      = $user->createToken($deviceName)->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }
}
