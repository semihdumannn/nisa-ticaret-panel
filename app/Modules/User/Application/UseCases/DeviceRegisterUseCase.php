<?php

namespace App\Modules\User\Application\UseCases;

use App\Models\User;
use App\Modules\User\Application\DTOs\DeviceRegisterDTO;
use App\Modules\User\Domain\Contracts\TotpServiceInterface;
use App\Modules\User\Domain\Contracts\UserRepositoryInterface;
use Spatie\Permission\Models\Role;

class DeviceRegisterUseCase
{
    public function __construct(
        private readonly TotpServiceInterface $totp,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Find or create a user by phone, ensure a TOTP secret exists, return Sanctum token.
     *
     * @return array{user: User, token: string, totp_secret: string, is_new: bool}
     */
    public function execute(DeviceRegisterDTO $dto): array
    {
        $user  = $this->userRepository->findByPhone($dto->phone);
        $isNew = false;

        if (! $user) {
            $isNew = true;
            $user  = $this->userRepository->create([
                'phone'       => $dto->phone,
                'name'        => $dto->name ?? 'User',
                'role'        => 'customer',
                'is_active'   => true,
                'totp_secret' => $this->totp->generateSecret(),
            ]);

            $user->profile()->create([]);
            Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
            $user->assignRole('customer');
        } elseif (! $user->totp_secret) {
            $this->userRepository->update($user, [
                'totp_secret' => $this->totp->generateSecret(),
            ]);
            $user->refresh();
        }

        $this->userRepository->update($user, ['last_login_at' => now()]);
        $user->refresh();

        $deviceName = $dto->deviceName ?? 'mobile';
        $token      = $user->createToken($deviceName)->plainTextToken;

        return [
            'user'        => $user,
            'token'       => $token,
            'totp_secret' => $user->totp_secret,
            'is_new'      => $isNew,
        ];
    }
}
