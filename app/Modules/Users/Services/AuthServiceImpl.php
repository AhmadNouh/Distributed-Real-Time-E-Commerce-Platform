<?php

namespace App\Modules\Users\Services;

use App\Modules\Users\Contracts\AuthService;
use App\Modules\Users\Contracts\UserService;
use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthServiceImpl implements AuthService
{
    public function __construct(
        private readonly UserService $users
    ) {}

    public function register(array $data): array
    {
        $user = $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'] ?? 'customer',
        ]);

        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function attemptLogin(array $credentials): ?array
    {
        if (! $token = JWTAuth::attempt($credentials)) {
            return null;
        }

        /** @var User $user */
        $user = auth('api')->user();

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }
}
