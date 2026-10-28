<?php

namespace App\Modules\Users\Services;

use App\Modules\Users\Models\User as ModelsUser;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth; 

class AuthService
{
    public function register(array $data): array
    {
        $user = ModelsUser::create([
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

        $user = auth('api')->user();

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout($user): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }
}