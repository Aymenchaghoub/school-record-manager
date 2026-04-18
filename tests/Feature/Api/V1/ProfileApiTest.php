<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->teacher()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $this->actingAs($user)
            ->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'jane@example.com')
            ->assertJsonPath('data.first_name', 'Jane')
            ->assertJsonPath('data.last_name', 'Doe');
    }

    public function test_authenticated_user_can_update_profile_and_password(): void
    {
        $user = User::factory()->student()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $payload = [
            'first_name' => 'New',
            'last_name' => 'Student',
            'email' => 'new.student@example.com',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ];

        $this->actingAs($user)
            ->putJson('/api/v1/profile', $payload)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Profile updated.')
            ->assertJsonPath('data.name', 'New Student')
            ->assertJsonPath('data.email', 'new.student@example.com');

        $user->refresh();

        $this->assertSame('New Student', $user->name);
        $this->assertSame('new.student@example.com', $user->email);
        $this->assertTrue(Hash::check('new-password-123', $user->password));
    }

    public function test_guest_cannot_access_profile_endpoints(): void
    {
        $this->getJson('/api/v1/profile')->assertStatus(401);
        $this->putJson('/api/v1/profile', [])->assertStatus(401);
    }
}