<?php

namespace Tests\Feature\Teacher;

use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\User;
use Tests\TestCase;

class TeacherFlowsTest extends TestCase
{
    public function test_teacher_can_list_own_grades(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->getJson('/api/v1/teacher/grades')
            ->assertOk();
    }

    public function test_teacher_can_create_grade(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        $class = ClassModel::factory()->create(['teacher_id' => $teacher->id]);
        $subject = Subject::factory()->create(['teacher_id' => $teacher->id]);

        $payload = [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'value' => 13,
            'type' => 'exam',
            'grade_date' => now()->toDateString(),
            'term' => 'Term 2',
        ];

        $this->actingAs($teacher)
            ->postJson('/api/v1/teacher/grades', $payload)
            ->assertCreated();
    }

    public function test_teacher_can_list_absences(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->getJson('/api/v1/teacher/absences')
            ->assertOk();
    }

    public function test_teacher_cannot_access_admin_routes(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->getJson('/api/v1/admin/users')
            ->assertForbidden();

        $this->actingAs($teacher)
            ->getJson('/api/v1/admin/grades')
            ->assertForbidden();
    }

    public function test_teacher_can_access_own_dashboard(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->getJson('/api/v1/dashboard/kpis')
            ->assertOk();
    }
}
