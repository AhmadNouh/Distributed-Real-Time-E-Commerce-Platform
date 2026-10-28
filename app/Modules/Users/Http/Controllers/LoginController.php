<?php

namespace App\Modules\Users\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseApiController;
use App\Modules\Users\Requests\Auth\LoginRequest;
use Modules\User\Http\Requests\Auth\RegisterRequest;
use App\Modules\Users\Resources\UserResource;
use App\Modules\Users\Services\AuthService; 


class LoginController extends BaseApiController
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string',
            'role' => 'nullable|string|in:customer,admin'
        ]);
    
        $result = $this->authService->register($validated);

        return $this->successDataResponse([
            'user' => UserResource::make($result['user']),
            'token' => $result['token'],
        ], 'User registered successfully', 201);
    }
    
    public function __invoke(LoginRequest $request)
    {
        $result = $this->authService->attemptLogin($request->validated());

        if (! $result) {
            return $this->unauthorizedResponse('Invalid credentials');
        }

        return $this->successDataResponse([
            'user' => UserResource::make($result['user']),
            'token' => $result['token'],
        ], 'Login successful');
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return $this->successResponse('Logged out successfully');
    }

    public function me(Request $request)
    {
        return $this->successDataResponse(
            UserResource::make($request->user()),
            'User data'
        );
    }
}