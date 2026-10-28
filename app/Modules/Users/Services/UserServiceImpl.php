<?php
namespace App\Modules\Users\Services;

use App\Modules\Users\Models\User;

class UserServiceImpl implements UserService
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }
}
