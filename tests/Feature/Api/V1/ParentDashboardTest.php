<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_access_parent_dashboard_endpoint(): void
    {
        $parent = User::factory()->parent()->create();
        $child = User::factory()->student()->create();

        $parent->parentChildren()->attach($child->id, [
            'relationship' => 'guardian',
            'is_primary_contact' => true,
        ]);

        $this->actingAs($parent)
            ->getJson('/api/v1/parent/dashboard')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'stats' => [
                        'total_children',
                        'average_grade',
                        'total_absences',
                        'upcoming_events',
                    ],
                ],
            ]);
    }

    public function test_student_cannot_access_parent_dashboard_endpoint(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->getJson('/api/v1/parent/dashboard')
            ->assertForbidden()
            ->assertJsonPath('message', 'Unauthorized.');
    }
}