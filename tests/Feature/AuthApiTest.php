<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Jose Sanches',
            'email' => 'jose@example.com',
            'password' => '12345678',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jose@example.com',
            'status' => 'active',
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'jose@example.com',
            'password' => bcrypt('12345678'),
            'password_hash' => bcrypt('12345678'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'jose@example.com',
            'password' => '12345678',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email'],
            ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'jose@example.com',
            'password' => bcrypt('12345678'),
            'password_hash' => bcrypt('12345678'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'jose@example.com',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_fetch_profile(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/user');

        $response
            ->assertOk()
            ->assertJsonPath('email', $user->email);
    }
}
