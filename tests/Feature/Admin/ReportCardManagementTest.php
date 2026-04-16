<?php

namespace Tests\Feature\Admin;

use App\Models\ClassModel;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Tests\TestCase;

class ReportCardManagementTest extends TestCase
{
    public function test_admin_can_list_report_cards(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/report-cards')
            ->assertOk();
    }

    public function test_student_cannot_access_admin_report_cards(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->getJson('/api/v1/admin/report-cards')
            ->assertForbidden();
    }

    public function test_report_card_averages_are_on_20_scale(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        $class = ClassModel::factory()->create(['teacher_id' => $teacher->id]);
        $subject = Subject::factory()->create();

        $class->subjects()->attach($subject->id, [
            'teacher_id' => $teacher->id,
            'hours_per_week' => 3,
            'room' => 'A1',
        ]);

        Grade::factory()->create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'value' => 14,
            'max_value' => 20,
            'type' => 'exam',
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/admin/report-cards', [
                'student_id' => $student->id,
                'class_id' => $class->id,
                'term' => 1,
                'year' => now()->year,
            ]);

        $response->assertCreated();

        $subjects = $response->json('data.subjects') ?? [];
        foreach ($subjects as $subjectData) {
            $this->assertEquals(20, $subjectData['max']);
        }
    }

    public function test_unauthenticated_cannot_access_report_cards(): void
    {
        $this->getJson('/api/v1/admin/report-cards')
            ->assertUnauthorized();
    }
}
