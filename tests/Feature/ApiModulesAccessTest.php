<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiModulesAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_all_module_endpoints(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->getJson('/api/admin/users')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['items', 'current_page', 'last_page', 'total'],
            ]);

        $this->actingAs($admin)->getJson('/api/admin/classes')->assertOk();
        $this->actingAs($admin)->getJson('/api/admin/subjects')->assertOk();
        $this->actingAs($admin)->getJson('/api/admin/grades')->assertOk();
        $this->actingAs($admin)->getJson('/api/admin/absences')->assertOk();
        $this->actingAs($admin)->getJson('/api/admin/report-cards')->assertOk();
        $this->actingAs($admin)->getJson('/api/admin/events')->assertOk();
    }

    public function test_teacher_cannot_access_admin_users_endpoint(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->getJson('/api/admin/users')
            ->assertForbidden();
    }

    public function test_student_and_parent_can_access_scoped_read_endpoints(): void
    {
        $student = User::factory()->student()->create();
        $parent = User::factory()->parent()->create();

        $parent->parentChildren()->attach($student->id, [
            'relationship' => 'guardian',
            'is_primary_contact' => true,
        ]);

        $this->actingAs($student)
            ->getJson('/api/student/grades')
            ->assertOk();

        $this->actingAs($parent)
            ->getJson('/api/parent/children/grades')
            ->assertOk();
    }
}
