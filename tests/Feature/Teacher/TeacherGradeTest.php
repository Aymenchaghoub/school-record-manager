<?php

namespace Tests\Feature\Teacher;

use App\Models\ClassModel;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Tests\TestCase;

class TeacherGradeTest extends TestCase
{
    public function test_teacher_can_list_only_own_grades(): void
    {
        $teacher = User::factory()->teacher()->create();
        $otherTeacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        $class = ClassModel::factory()->create(['teacher_id' => $teacher->id]);
        $subject = Subject::factory()->create();

        $mine = Grade::factory()->create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);

        Grade::factory()->create([
            'teacher_id' => $otherTeacher->id,
            'student_id' => $student->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);

        $response = $this->actingAs($teacher)
            ->getJson('/api/v1/teacher/grades')
            ->assertOk();

        $ids = collect($response->json('data.items'))->pluck('id')->all();
        $this->assertContains($mine->id, $ids);
    }

    public function test_teacher_cannot_access_admin_grade_endpoint(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->getJson('/api/v1/admin/grades')
            ->assertForbidden();
    }
}
