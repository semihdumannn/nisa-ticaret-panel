<?php

namespace App\Modules\User\Domain\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByFirebaseUid(string $uid): ?User;
    public function findByPhone(string $phone): ?User;
    public function findByEmail(string $email): ?User;
    public function create(array $data): User;
    public function update(User $user, array $data): User;
    public function paginate(int $perPage = 20): LengthAwarePaginator;
}
