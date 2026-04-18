<?php

namespace Tests\Feature;

use App\Models\ClassModel;
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
                ->getJson('/api/v1/admin/users')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['items', 'current_page', 'last_page', 'total'],
            ]);

            $this->actingAs($admin)->getJson('/api/v1/admin/classes')->assertOk();
            $this->actingAs($admin)->getJson('/api/v1/admin/subjects')->assertOk();
            $this->actingAs($admin)->getJson('/api/v1/admin/grades')->assertOk();
            $this->actingAs($admin)->getJson('/api/v1/admin/absences')->assertOk();
            $this->actingAs($admin)->getJson('/api/v1/admin/report-cards')->assertOk();
            $this->actingAs($admin)->getJson('/api/v1/admin/events')->assertOk();
    }

    public function test_teacher_cannot_access_admin_users_endpoint(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
              ->getJson('/api/v1/admin/users')
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
                ->getJson('/api/v1/student/grades')
            ->assertOk();

        $this->actingAs($student)
                ->getJson('/api/v1/student/report-cards')
            ->assertOk();

        $this->actingAs($parent)
                ->getJson('/api/v1/parent/children/grades')
            ->assertOk();

        $this->actingAs($parent)
                ->getJson('/api/v1/parent/report-cards')
            ->assertOk();
    }

    public function test_admin_classes_list_honors_per_page_parameter(): void
    {
        $admin = User::factory()->admin()->create();
        ClassModel::factory()->count(6)->create();

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/classes?per_page=3')
            ->assertOk();

        $this->assertSame(3, (int) $response->json('data.per_page'));
        $this->assertCount(3, $response->json('data.items'));
    }
}
