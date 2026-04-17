<?php

namespace Tests\Feature\Teacher;

use App\Models\Absence;
use App\Models\User;
use Tests\TestCase;

class TeacherAbsenceTest extends TestCase
{
    public function test_teacher_can_list_own_absences(): void
    {
        $teacher = User::factory()->teacher()->create();
        Absence::factory()->count(2)->create(['recorded_by' => $teacher->id]);

        $this->actingAs($teacher)
            ->getJson('/api/v1/teacher/absences')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_teacher_absence_list_honors_per_page_parameter(): void
    {
        $teacher = User::factory()->teacher()->create();
        Absence::factory()->count(7)->create(['recorded_by' => $teacher->id]);

        $response = $this->actingAs($teacher)
            ->getJson('/api/v1/teacher/absences?per_page=3')
            ->assertOk();

        $this->assertSame(3, (int) $response->json('data.per_page'));
        $this->assertCount(3, $response->json('data.items'));
    }

    public function test_teacher_cannot_access_admin_absence_endpoint(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->getJson('/api/v1/admin/absences')
            ->assertForbidden();
    }
}
