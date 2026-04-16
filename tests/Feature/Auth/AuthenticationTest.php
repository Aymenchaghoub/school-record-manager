<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_user_can_login_from_web_form(): void
    {
        User::factory()->create([
            'email' => 'auth-web@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'auth-web@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create([
            'email' => 'auth-web-invalid@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'auth-web-invalid@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
