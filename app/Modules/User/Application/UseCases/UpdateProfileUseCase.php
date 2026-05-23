<?php

namespace App\Modules\User\Application\UseCases;

use App\Models\User;
use App\Modules\User\Application\DTOs\UpdateProfileDTO;
use App\Modules\User\Domain\Contracts\UserRepositoryInterface;

class UpdateProfileUseCase
{
    public function __construct(private readonly UserRepositoryInterface $userRepository) {}

    public function execute(User $user, UpdateProfileDTO $dto): User
    {
        // Update user core fields
        $userUpdates = array_filter([
            'name'  => $dto->name,
            'email' => $dto->email,
        ], fn ($v) => $v !== null);

        if ($userUpdates) {
            $this->userRepository->update($user, $userUpdates);
            $user->refresh();
        }

        // Update profile fields
        $profileUpdates = array_filter([
            'company_name' => $dto->companyName,
            'tax_number'   => $dto->taxNumber,
        ], fn ($v) => $v !== null);

        if ($profileUpdates) {
            $user->profile()->updateOrCreate(['user_id' => $user->id], $profileUpdates);
            $user->load('profile');
        }

        return $user;
    }
}
