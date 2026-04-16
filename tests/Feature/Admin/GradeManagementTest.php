<?php

namespace Tests\Feature\Admin;

use App\Models\ClassModel;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Tests\TestCase;

class GradeManagementTest extends TestCase
{
    public function test_admin_can_list_grades(): void
    {
        $admin = User::factory()->admin()->create();
        Grade::factory()->count(5)->create();

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/grades')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['items', 'current_page', 'last_page', 'total'],
            ]);
    }

    public function test_teacher_cannot_access_admin_grades(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->getJson('/api/v1/admin/grades')
            ->assertForbidden();
    }

    public function test_admin_can_create_grade(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        $class = ClassModel::factory()->create(['teacher_id' => $teacher->id]);
        $subject = Subject::factory()->create();

        $payload = [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'value' => 14,
            'type' => 'exam',
            'grade_date' => now()->toDateString(),
            'term' => 'Term 1',
            'weight' => 1,
        ];

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/grades', $payload)
            ->assertCreated()
            ->assertJsonPath('data.student_id', $student->id)
            ->assertJsonPath('data.subject_id', $subject->id);
    }

    public function test_grade_value_must_be_between_zero_and_twenty(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        $class = ClassModel::factory()->create(['teacher_id' => $teacher->id]);
        $subject = Subject::factory()->create();

        $payload = [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'value' => 25,
            'type' => 'exam',
            'grade_date' => now()->toDateString(),
        ];

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/grades', $payload)
            ->assertUnprocessable();
    }

    public function test_grade_value_above_20_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        $class = ClassModel::factory()->create(['teacher_id' => $teacher->id]);
        $subject = Subject::factory()->create();

        $payload = [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'value' => 25,
            'type' => 'exam',
            'grade_date' => now()->toDateString(),
            'term' => 'Term 1',
        ];

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/grades', $payload)
            ->assertUnprocessable();
    }

    public function test_grade_value_below_0_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        $class = ClassModel::factory()->create(['teacher_id' => $teacher->id]);
        $subject = Subject::factory()->create();

        $payload = [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'value' => -1,
            'type' => 'exam',
            'grade_date' => now()->toDateString(),
            'term' => 'Term 1',
        ];

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/grades', $payload)
            ->assertUnprocessable();
    }

    public function test_student_cannot_create_grade(): void
    {
        $student = User::factory()->student()->create();
        $teacher = User::factory()->teacher()->create();
        $class = ClassModel::factory()->create(['teacher_id' => $teacher->id]);
        $subject = Subject::factory()->create();

        $payload = [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'value' => 14,
            'type' => 'exam',
            'grade_date' => now()->toDateString(),
            'term' => 'Term 1',
        ];

        $this->actingAs($student)
            ->postJson('/api/v1/admin/grades', $payload)
            ->assertForbidden();
    }

    public function test_parent_cannot_create_grade(): void
    {
        $parent = User::factory()->parent()->create();
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        $class = ClassModel::factory()->create(['teacher_id' => $teacher->id]);
        $subject = Subject::factory()->create();

        $payload = [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'value' => 14,
            'type' => 'exam',
            'grade_date' => now()->toDateString(),
            'term' => 'Term 1',
        ];

        $this->actingAs($parent)
            ->postJson('/api/v1/admin/grades', $payload)
            ->assertForbidden();
    }

    public function test_admin_can_update_grade(): void
    {
        $admin = User::factory()->admin()->create();
        $grade = Grade::factory()->create(['value' => 10]);

        $response = $this->actingAs($admin)
            ->putJson("/api/v1/admin/grades/{$grade->id}", ['value' => 16, 'type' => 'exam', 'term' => 'Term 1'])
            ->assertOk();

        $this->assertSame(16.0, (float) $response->json('data.value'));
    }

    public function test_admin_can_delete_grade(): void
    {
        $admin = User::factory()->admin()->create();
        $grade = Grade::factory()->create();

        $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/grades/{$grade->id}")
            ->assertOk();

        $this->assertDatabaseMissing('grades', ['id' => $grade->id]);
    }
}
