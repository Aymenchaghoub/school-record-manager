<?php

namespace Tests\Feature\Student;

use App\Models\User;
use Tests\TestCase;

class StudentFlowsTest extends TestCase
{
    public function test_student_can_view_own_grades(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->getJson('/api/v1/student/grades')
            ->assertOk();
    }

    public function test_student_can_view_own_absences(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->getJson('/api/v1/student/absences')
            ->assertOk();
    }

    public function test_student_cannot_access_teacher_routes(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->getJson('/api/v1/teacher/grades')
            ->assertForbidden();

        $this->actingAs($student)
            ->postJson('/api/v1/teacher/grades', [])
            ->assertForbidden();
    }

    public function test_student_cannot_access_admin_routes(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->getJson('/api/v1/admin/users')
            ->assertForbidden();
    }

    public function test_student_can_access_own_dashboard(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->getJson('/api/v1/dashboard/kpis')
            ->assertOk();
    }
}
