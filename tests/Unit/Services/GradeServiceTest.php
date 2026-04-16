<?php

namespace Tests\Unit\Services;

use App\Models\ClassModel;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use App\Services\GradeService;
use Tests\TestCase;

class GradeServiceTest extends TestCase
{
    private GradeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GradeService();
    }

    public function test_average_returns_correct_value(): void
    {
        $student = User::factory()->student()->create();
        $subject = Subject::factory()->create();

        Grade::factory()->count(3)->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'value' => 15,
            'max_value' => 20,
        ]);

        $avg = $this->service->averageForStudent($student->id, $subject->id);

        $this->assertSame(15.0, $avg);
    }

    public function test_class_stats_returns_expected_aggregate_values(): void
    {
        $teacher = User::factory()->teacher()->create();
        $class = ClassModel::factory()->create(['teacher_id' => $teacher->id]);
        $subject = Subject::factory()->create();

        $studentA = User::factory()->student()->create();
        $studentB = User::factory()->student()->create();

        $studentA->studentClasses()->attach($class->id, ['enrollment_date' => now(), 'status' => 'active']);
        $studentB->studentClasses()->attach($class->id, ['enrollment_date' => now(), 'status' => 'active']);

        Grade::factory()->create([
            'student_id' => $studentA->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'value' => 12,
            'max_value' => 20,
        ]);

        Grade::factory()->create([
            'student_id' => $studentB->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'value' => 18,
            'max_value' => 20,
        ]);

        $stats = $this->service->classStats($class->id, $subject->id);

        $this->assertSame(2, $stats['count']);
        $this->assertSame(12.0, $stats['min']);
        $this->assertSame(18.0, $stats['max']);
        $this->assertSame(15.0, $stats['average']);
    }

    public function test_performance_label_excellent(): void
    {
        $this->assertSame('Excellent', $this->service->performanceLabel(18));
    }

    public function test_performance_label_insuffisant(): void
    {
        $this->assertSame('Insuffisant', $this->service->performanceLabel(7));
    }
}
