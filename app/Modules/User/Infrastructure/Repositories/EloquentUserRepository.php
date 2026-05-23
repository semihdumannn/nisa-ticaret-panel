<?php

namespace App\Modules\User\Infrastructure\Repositories;

use App\Models\User;
use App\Modules\User\Domain\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByFirebaseUid(string $uid): ?User
    {
        return User::where('firebase_uid', $uid)->first();
    }

    public function findByPhone(string $phone): ?User
    {
        return User::where('phone', $phone)->first();
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return User::latest()->paginate($perPage);
    }
}
