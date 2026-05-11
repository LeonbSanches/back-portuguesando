<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiErrorFormatTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_requests_follow_error_contract(): void
    {
        $response = $this->getJson('/api/subjects');

        $response
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'unauthenticated')
            ->assertJsonPath('message', 'Não autenticado.');
    }

    public function test_not_found_responses_follow_error_contract(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/topics/999999');

        $response
            ->assertStatus(404)
            ->assertJsonPath('error_code', 'not_found')
            ->assertJsonPath('message', 'Recurso não encontrado.');
    }

    public function test_validation_errors_follow_error_contract(): void
    {
        $response = $this->postJson('/api/auth/register', []);

        $response
            ->assertStatus(422)
            ->assertJsonPath('error_code', 'validation_error')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => ['name', 'email', 'password'],
            ]);
    }
}
