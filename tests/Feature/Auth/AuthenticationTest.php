<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $admin = User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => 'admin'
        ]);

        $this->actingAs($admin);

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'user'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'response' => [
                    'data' => [
                        'role',
                        'name',
                        'email',
                        'id'
                    ]
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com'
        ]);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'name' => 'Test user',
            'email' => 'user@example.com',
            'password' => 'password123',
            'role' => 'user'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'response' => [
                    'data' => [
                        'role',
                        'name',
                        'email',
                        'id'
                    ],
                    'token_type',
                    'access_token'
                ]
            ]);
    }

    public function test_user_can_logout()
    {
        $user = $this->actingAsUser();

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200);
    }

    public function test_user_can_get_their_profile()
    {
        $user = $this->actingAsUser();

        $response = $this->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'response' => [
                    'data' => [
                        'role',
                        'name',
                        'email',
                        'id'
                    ]
                ]
            ]);
    }
} 