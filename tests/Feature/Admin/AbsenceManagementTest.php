<?php

namespace Tests\Feature\Admin;

use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\User;
use Tests\TestCase;

class AbsenceManagementTest extends TestCase
{
    public function test_admin_can_list_absences(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/absences')
            ->assertOk()
            ->assertJsonStructure(['data']);
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
            'absence_date' => now()->toDateString(),
            'is_justified' => false,
            'type' => 'full_day',
            'recorded_by' => $teacher->id,
        ];

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/absences', $payload)
            ->assertCreated();
    }

    public function test_teacher_cannot_access_admin_absences(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->getJson('/api/v1/admin/absences')
            ->assertForbidden();
    }

    public function test_student_cannot_access_admin_absences(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->getJson('/api/v1/admin/absences')
            ->assertForbidden();
    }

    public function test_unauthenticated_cannot_access_absences(): void
    {
        $this->getJson('/api/v1/admin/absences')->assertUnauthorized();
    }
}
