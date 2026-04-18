<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SanctumAuthTest extends TestCase
{
    private function primeCsrfCookie(): void
    {
        $this->withHeader('Origin', 'http://localhost:5173')
            ->get('/sanctum/csrf-cookie')
            ->assertNoContent();
    }

    public function test_user_can_login_and_fetch_profile_with_session_cookie(): void
    {
        User::factory()->create([
            'email' => 'sanctum-v1@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->primeCsrfCookie();

        $this->withHeader('Origin', 'http://localhost:5173')
            ->postJson('/api/v1/login', [
                'email' => 'sanctum-v1@example.com',
                'password' => 'password123',
            ])
            ->assertOk()
            ->assertJsonPath('user.email', 'sanctum-v1@example.com');

        $this->withHeader('Origin', 'http://localhost:5173')
            ->getJson('/api/v1/user')
            ->assertOk()
            ->assertJsonPath('user.email', 'sanctum-v1@example.com');
    }
}
