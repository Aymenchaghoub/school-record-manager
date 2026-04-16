<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/users')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_teacher_cannot_list_admin_users(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->getJson('/api/v1/admin/users')
            ->assertForbidden();
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->admin()->create();

        $payload = [
            'name' => 'Created Teacher',
            'email' => 'created-teacher@example.com',
            'password' => 'password123',
            'role' => 'teacher',
            'is_active' => true,
        ];

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/users', $payload)
            ->assertCreated()
            ->assertJsonPath('data.email', 'created-teacher@example.com');
    }
}
