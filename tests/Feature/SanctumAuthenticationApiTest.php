<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SanctumAuthenticationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'session.driver' => 'file',
            'sanctum.stateful' => [
                'localhost:5173',
                '127.0.0.1:5173',
                'localhost:8000',
                '127.0.0.1:8000',
                'localhost',
                '127.0.0.1',
            ],
        ]);
    }

    /**
     * Prime the CSRF cookie for SPA authentication flow.
     */
    private function primeCsrfCookie(): void
    {
        $this->withHeader('Origin', 'http://localhost:5173')
            ->get('/sanctum/csrf-cookie')
            ->assertNoContent();
    }

    /**
     * Test API login and current user retrieval using Sanctum session cookies.
     */
    public function test_api_login_and_user_endpoint_work_with_session_cookie()
    {
        User::factory()->create([
            'email' => 'api-user@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->primeCsrfCookie();

        $loginResponse = $this->withHeader('Origin', 'http://localhost:5173')
            ->postJson('/api/v1/login', [
                'email' => 'api-user@example.com',
                'password' => 'password',
                'remember' => true,
            ]);

        $loginResponse
            ->assertOk()
            ->assertJsonPath('user.email', 'api-user@example.com');

        $userResponse = $this->withHeader('Origin', 'http://localhost:5173')
            ->getJson('/api/v1/user');

        $userResponse
            ->assertOk()
            ->assertJsonPath('user.email', 'api-user@example.com');
    }

    /**
     * Test API login rejects invalid credentials.
     */
    public function test_api_login_rejects_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'api-user@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $this->primeCsrfCookie();

        $response = $this->withHeader('Origin', 'http://localhost:5173')
            ->postJson('/api/v1/login', [
                'email' => 'api-user@example.com',
                'password' => 'wrong-password',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test API logout invalidates the session.
     */
    public function test_api_logout_invalidates_session()
    {
        User::factory()->create([
            'email' => 'api-user@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $this->primeCsrfCookie();

        $this->withHeader('Origin', 'http://localhost:5173')
            ->postJson('/api/v1/login', [
                'email' => 'api-user@example.com',
                'password' => 'password',
            ])
            ->assertOk();

        $this->withHeader('Origin', 'http://localhost:5173')
            ->postJson('/api/v1/logout')
            ->assertOk();

        $this->refreshApplication();

        $this->withHeader('Origin', 'http://localhost:5173')
            ->getJson('/api/v1/user')
            ->assertStatus(401);
    }
}
