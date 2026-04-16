<?php

namespace Tests\Feature\Admin;

use App\Models\Absence;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\User;
use Tests\TestCase;

class AbsenceManagementTest extends TestCase
{
    public function test_admin_can_list_absences(): void
    {
        $admin = User::factory()->admin()->create();
        Absence::factory()->count(3)->create();

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/absences')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_student_cannot_access_admin_absences(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->getJson('/api/v1/admin/absences')
            ->assertForbidden();
    }

    public function test_admin_can_create_absence(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        $class = ClassModel::factory()->create(['teacher_id' => $teacher->id]);
        $subject = Subject::factory()->create();

        $payload = [
            'student_id' => $student->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'recorded_by' => $teacher->id,
            'absence_date' => now()->toDateString(),
            'type' => 'full_day',
            'reason' => 'Medical appointment',
            'is_justified' => true,
        ];

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/absences', $payload)
            ->assertCreated()
            ->assertJsonPath('data.student_id', $student->id);
    }
}
