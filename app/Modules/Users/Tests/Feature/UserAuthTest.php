<?php

namespace Modules\User\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Modules\User\Models\User;

class UserAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_login_and_get_profile()
    {
        $this->withoutExceptionHandling();
        
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Ahmad Nouh',
            'email' => 'ahmad@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['success', 'data' => ['token', 'user']]);

        $token = $response->json('data.token');

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'ahmad@test.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
                      ->assertJsonStructure(['success', 'data' => ['token']]);

        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
                           ->getJson('/api/v1/auth/me');

        $meResponse->assertStatus(200)
                    ->assertJsonPath('data.email', 'ahmad@test.com');
    }
}