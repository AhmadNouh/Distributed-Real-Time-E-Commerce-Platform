<?php

namespace App\Modules\Users\Contracts;

use App\Modules\Users\Models\User;

/**
 * Public contract of the Users module.
 *
 * Other modules must resolve users through this interface instead of
 * querying the users table or the User model directly.
 */
interface UserService
{
    public function find(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function create(array $data): User;

    public function isAdmin(User $user): bool;
}
