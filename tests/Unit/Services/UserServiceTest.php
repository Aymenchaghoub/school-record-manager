<?php

namespace Tests\Unit\Services;

use App\Models\ClassModel;
use App\Services\UserService;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    private UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserService();
    }

    public function test_create_student_assigns_class_relation(): void
    {
        $class = ClassModel::factory()->create();

        $student = $this->service->create([
            'name' => 'Student Service Test',
            'email' => 'student-service-test@example.com',
            'password' => 'password123',
            'role' => 'student',
            'class_id' => $class->id,
            'is_active' => true,
        ]);

        $this->assertSame('student', $student->role);
        $this->assertTrue(Hash::check('password123', $student->password));
        $this->assertDatabaseHas('student_classes', [
            'student_id' => $student->id,
            'class_id' => $class->id,
            'status' => 'active',
        ]);
    }

    public function test_get_user_statistics_returns_student_metrics(): void
    {
        $class = ClassModel::factory()->create();

        $student = $this->service->create([
            'name' => 'Student Stats Test',
            'email' => 'student-stats-test@example.com',
            'password' => 'password123',
            'role' => 'student',
            'class_id' => $class->id,
            'is_active' => true,
        ]);

        $stats = $this->service->getUserStatistics($student);

        $this->assertArrayHasKey('current_class', $stats);
        $this->assertArrayHasKey('total_subjects', $stats);
        $this->assertArrayHasKey('total_grades', $stats);
        $this->assertArrayHasKey('total_absences', $stats);
    }
}
