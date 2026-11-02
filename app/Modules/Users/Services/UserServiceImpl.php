<?php

namespace App\Modules\Users\Services;

use App\Modules\Users\Contracts\UserService;
use App\Modules\Users\Models\User;

class UserServiceImpl implements UserService
{
    public function find(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function isAdmin(User $user): bool
    {
        return $user->isAdmin();
    }
}
