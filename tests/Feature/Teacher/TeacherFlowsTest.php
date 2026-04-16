<?php

namespace Tests\Feature\Teacher;

use App\Models\ClassModel;
use App\Models\Grade;
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

    public function test_unauthenticated_cannot_access_teacher_grades(): void
    {
        $this->getJson('/api/v1/teacher/grades')->assertUnauthorized();
    }

    public function test_teacher_can_view_own_grade_details(): void
    {
        $teacher = User::factory()->teacher()->create();
        $grade = Grade::factory()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher)
            ->getJson("/api/v1/teacher/grades/{$grade->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $grade->id);
    }

    public function test_teacher_cannot_view_other_teacher_grade_details(): void
    {
        $teacher = User::factory()->teacher()->create();
        $otherTeacher = User::factory()->teacher()->create();
        $grade = Grade::factory()->create(['teacher_id' => $otherTeacher->id]);

        $this->actingAs($teacher)
            ->getJson("/api/v1/teacher/grades/{$grade->id}")
            ->assertNotFound();
    }

    public function test_teacher_can_update_own_grade(): void
    {
        $teacher = User::factory()->teacher()->create();
        $grade = Grade::factory()->create(['teacher_id' => $teacher->id, 'value' => 11]);

        $response = $this->actingAs($teacher)
            ->putJson("/api/v1/teacher/grades/{$grade->id}", ['value' => 15, 'type' => 'exam'])
            ->assertOk();

        $this->assertSame(15.0, (float) $response->json('data.value'));
    }

    public function test_teacher_can_delete_own_grade(): void
    {
        $teacher = User::factory()->teacher()->create();
        $grade = Grade::factory()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher)
            ->deleteJson("/api/v1/teacher/grades/{$grade->id}")
            ->assertOk();

        $this->assertDatabaseMissing('grades', ['id' => $grade->id]);
    }

    public function test_teacher_cannot_delete_other_teacher_grade(): void
    {
        $teacher = User::factory()->teacher()->create();
        $otherTeacher = User::factory()->teacher()->create();
        $grade = Grade::factory()->create(['teacher_id' => $otherTeacher->id]);

        $this->actingAs($teacher)
            ->deleteJson("/api/v1/teacher/grades/{$grade->id}")
            ->assertNotFound();
    }
}
