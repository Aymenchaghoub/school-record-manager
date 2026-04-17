<?php

namespace Tests\Feature\Parent;

use App\Models\Absence;
use App\Models\User;
use Tests\TestCase;

class ParentFlowsTest extends TestCase
{
    public function test_parent_can_view_children_grades(): void
    {
        $parent = User::factory()->parent()->create();

        $this->actingAs($parent)
            ->getJson('/api/v1/parent/children/grades')
            ->assertOk();
    }

    public function test_parent_can_view_children_absences(): void
    {
        $parent = User::factory()->parent()->create();

        $this->actingAs($parent)
            ->getJson('/api/v1/parent/children/absences')
            ->assertOk();
    }

    public function test_parent_children_absence_list_honors_per_page_parameter(): void
    {
        $parent = User::factory()->parent()->create();
        $student = User::factory()->student()->create();

        $parent->parentChildren()->attach($student->id, [
            'relationship' => 'guardian',
            'is_primary_contact' => true,
        ]);

        Absence::factory()->count(7)->create(['student_id' => $student->id]);

        $response = $this->actingAs($parent)
            ->getJson('/api/v1/parent/children/absences?per_page=4')
            ->assertOk();

        $this->assertSame(4, (int) $response->json('data.per_page'));
        $this->assertCount(4, $response->json('data.items'));
    }

    public function test_parent_can_access_dashboard(): void
    {
        $parent = User::factory()->parent()->create();

        $this->actingAs($parent)
            ->getJson('/api/v1/dashboard/kpis')
            ->assertOk();
    }

    public function test_student_cannot_access_parent_routes(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->getJson('/api/v1/parent/children/grades')
            ->assertForbidden();
    }

    public function test_teacher_cannot_access_parent_routes(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->getJson('/api/v1/parent/children/grades')
            ->assertForbidden();
    }

    public function test_unauthenticated_cannot_access_parent_routes(): void
    {
        $this->getJson('/api/v1/parent/children/grades')
            ->assertUnauthorized();
    }
}
