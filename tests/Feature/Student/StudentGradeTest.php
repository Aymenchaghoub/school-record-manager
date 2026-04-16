<?php

namespace Tests\Feature\Student;

use App\Models\ClassModel;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Tests\TestCase;

class StudentGradeTest extends TestCase
{
    public function test_student_can_list_own_grades(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        $otherStudent = User::factory()->student()->create();
        $class = ClassModel::factory()->create(['teacher_id' => $teacher->id]);
        $subject = Subject::factory()->create();

        $ownGrade = Grade::factory()->create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);

        Grade::factory()->create([
            'student_id' => $otherStudent->id,
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);

        $response = $this->actingAs($student)
            ->getJson('/api/v1/student/grades')
            ->assertOk();

        $ids = collect($response->json('data.items'))->pluck('id')->all();
        $this->assertContains($ownGrade->id, $ids);
    }

    public function test_student_cannot_access_admin_grades(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->getJson('/api/v1/admin/grades')
            ->assertForbidden();
    }
}
