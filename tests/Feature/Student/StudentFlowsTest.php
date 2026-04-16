<?php

namespace Tests\Feature\Student;

use App\Models\Absence;
use App\Models\Grade;
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

    public function test_unauthenticated_cannot_access_student_routes(): void
    {
        $this->getJson('/api/v1/student/grades')->assertUnauthorized();
        $this->getJson('/api/v1/student/absences')->assertUnauthorized();
    }

    public function test_student_can_view_own_grade_details(): void
    {
        $student = User::factory()->student()->create();
        $grade = Grade::factory()->create(['student_id' => $student->id]);

        $this->actingAs($student)
            ->getJson("/api/v1/student/grades/{$grade->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $grade->id);
    }

    public function test_student_cannot_view_other_student_grade_details(): void
    {
        $student = User::factory()->student()->create();
        $otherStudent = User::factory()->student()->create();
        $grade = Grade::factory()->create(['student_id' => $otherStudent->id]);

        $this->actingAs($student)
            ->getJson("/api/v1/student/grades/{$grade->id}")
            ->assertNotFound();
    }

    public function test_student_can_view_own_absence_details(): void
    {
        $student = User::factory()->student()->create();
        $absence = Absence::factory()->create(['student_id' => $student->id]);

        $this->actingAs($student)
            ->getJson("/api/v1/student/absences/{$absence->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $absence->id);
    }

    public function test_student_cannot_view_other_student_absence_details(): void
    {
        $student = User::factory()->student()->create();
        $otherStudent = User::factory()->student()->create();
        $absence = Absence::factory()->create(['student_id' => $otherStudent->id]);

        $this->actingAs($student)
            ->getJson("/api/v1/student/absences/{$absence->id}")
            ->assertNotFound();
    }

    public function test_student_cannot_access_parent_routes(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->getJson('/api/v1/parent/dashboard')
            ->assertForbidden();
    }
}
