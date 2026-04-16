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
}
