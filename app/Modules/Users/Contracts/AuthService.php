<?php

namespace App\Modules\Users\Contracts;

use App\Modules\Users\Models\User;

interface AuthService
{
    /**
     * @return array{user: User, token: string}
     */
    public function register(array $data): array;

    /**
     * @return array{user: User, token: string}|null
     */
    public function attemptLogin(array $credentials): ?array;

    public function logout(User $user): void;
}
