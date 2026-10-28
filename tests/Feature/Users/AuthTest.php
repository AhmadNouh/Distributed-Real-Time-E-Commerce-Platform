<?php

namespace Tests\Feature\Users;

use Illuminate\Foundation\Testing\RefreshDatabase; 
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase; 

    public function test_register_login_me() {
        $r = $this->postJson('/api/v1/auth/register', [
            'name' => 'T',
            'email' => 't@t.com',
            'password' => 'secret',
            'password_confirmation' => 'secret'
        ]);
        
        $r->assertStatus(201)->assertJsonStructure(['token']);
        $token = $r->json('token');
        
        $this->postJson('/api/v1/auth/login', [
            'email' => 't@t.com',
            'password' => 'secret'
        ])->assertStatus(200);
        
        $this->withHeader('Authorization', 'Bearer ' . $token)
             ->getJson('/api/v1/auth/me')
             ->assertStatus(200);
    }
}