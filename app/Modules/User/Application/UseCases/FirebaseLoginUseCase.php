<?php

namespace App\Modules\User\Application\UseCases;

use App\Models\User;
use App\Modules\User\Application\DTOs\FirebaseLoginDTO;
use App\Modules\User\Domain\Contracts\FirebaseAuthInterface;
use App\Modules\User\Domain\Contracts\UserRepositoryInterface;
use Spatie\Permission\Models\Role;

class FirebaseLoginUseCase
{
    public function __construct(
        private readonly FirebaseAuthInterface $firebaseAuth,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Verify Firebase token, upsert user, return Sanctum token.
     *
     * @return array{user: User, token: string, is_new: bool}
     */
    public function execute(FirebaseLoginDTO $dto): array
    {
        // 1. Verify Firebase ID token
        $payload = $this->firebaseAuth->verifyIdToken($dto->idToken);

        $uid         = $payload['uid'];
        $phoneNumber = $payload['phone_number'];
        $email       = $payload['email'];
        $name        = $payload['name'] ?? 'User';

        // 2. Find or create user
        $user = $this->userRepository->findByFirebaseUid($uid);
        $isNew = false;

        if (! $user && $phoneNumber) {
            $user = $this->userRepository->findByPhone($phoneNumber);
        }

        if (! $user) {
            $isNew = true;
            $user  = $this->userRepository->create([
                'firebase_uid' => $uid,
                'phone'        => $phoneNumber ?? $uid, // fallback when phone not in token
                'name'         => $name,
                'email'        => $email,
                'role'         => 'customer',
                'is_active'    => true,
            ]);

            // Create empty profile and assign Spatie role
            $user->profile()->create([]);
            Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
            $user->assignRole('customer');
        } else {
            $updates = ['last_login_at' => now()];
            if (! $user->firebase_uid) {
                $updates['firebase_uid'] = $uid;
            }
            if ($email && ! $user->email) {
                $updates['email'] = $email;
            }
            $this->userRepository->update($user, $updates);
            $user->refresh();
        }

        // 3. Generate Sanctum token
        $deviceName = $dto->deviceName ?? 'mobile';
        $token      = $user->createToken($deviceName)->plainTextToken;

        return [
            'user'   => $user,
            'token'  => $token,
            'is_new' => $isNew,
        ];
    }
}
