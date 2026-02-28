<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login page is accessible
     */
    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * Test users can authenticate with valid credentials
     */
    public function test_users_can_authenticate_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));
    }

    /**
     * Test users cannot authenticate with invalid credentials
     */
    public function test_users_cannot_authenticate_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test inactive users cannot login
     */
    public function test_inactive_users_cannot_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test different roles redirect to correct dashboards
     */
    public function test_different_roles_redirect_correctly()
    {
        $roles = [
            'admin' => 'admin.dashboard',
            'teacher' => 'teacher.dashboard',
            'student' => 'student.dashboard',
            'parent' => 'parent.dashboard',
        ];

        foreach ($roles as $role => $routeName) {
            $user = User::factory()->create([
                'role' => $role,
                'is_active' => true,
            ]);

            $response = $this->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

            $response->assertRedirect(route($routeName));
            
            $this->post('/logout');
        }
    }

    /**
     * Test authenticated users can logout
     */
    public function test_authenticated_users_can_logout()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);
        
        $response = $this->post('/logout');
        
        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    /**
     * Test authenticated users are redirected from login page
     */
    public function test_authenticated_users_redirected_from_login()
    {
        $user = User::factory()->create(['role' => 'admin']);
        
        $this->actingAs($user);
        
        $response = $this->get('/login');
        
        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Test password reset functionality
     */
    public function test_users_can_reset_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);
        
        $this->actingAs($user);
        
        $response = $this->post('/password/reset', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);
        
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        
        // Verify password was changed
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    /**
     * Test wrong current password fails reset
     */
    public function test_wrong_current_password_fails_reset()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);
        
        $this->actingAs($user);
        
        $response = $this->post('/password/reset', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);
        
        $response->assertSessionHasErrors('current_password');
    }
}
