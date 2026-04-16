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
            ->assertJsonStructure(['data']);
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->admin()->create();

        $payload = [
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@test.com',
            'password' => 'password123',
            'role' => 'student',
        ];

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/users', $payload)
            ->assertCreated()
            ->assertJsonPath('data.email', 'jean.dupont@test.com');
    }

    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->student()->create();

        $this->actingAs($admin)
            ->putJson("/api/v1/admin/users/{$target->id}", ['name' => 'Updated Name'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_admin_can_delete_user(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->student()->create();

        $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/users/{$target->id}")
            ->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_teacher_cannot_access_user_management(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->getJson('/api/v1/admin/users')
            ->assertForbidden();
    }

    public function test_student_cannot_access_user_management(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->getJson('/api/v1/admin/users')
            ->assertForbidden();
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['email' => 'taken@test.com']);

        $payload = [
            'name' => 'A B',
            'email' => 'taken@test.com',
            'password' => 'password123',
            'role' => 'student',
        ];

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/users', $payload)
            ->assertUnprocessable();
    }
}
