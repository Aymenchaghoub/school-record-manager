<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\Absence;
use App\Models\ReportCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCardTest extends TestCase
{
    use RefreshDatabase;

    private $student;
    private $class;
    private $subjects;
    private $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create teacher
        $this->teacher = User::factory()->create([
            'role' => 'teacher',
            'is_active' => true,
        ]);
        
        // Create class
        $this->class = ClassModel::factory()->create([
            'responsible_teacher_id' => $this->teacher->id,
            'academic_year' => '2024-2025',
        ]);
        
        // Create subjects
        $this->subjects = Subject::factory()->count(5)->create();
        
        // Assign subjects to class with teacher
        foreach ($this->subjects as $subject) {
            $this->class->subjects()->attach($subject->id, [
                'teacher_id' => $this->teacher->id,
                'hours_per_week' => rand(2, 4),
            ]);
        }
        
        // Create student and enroll in class
        $this->student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);
        
        $this->student->studentClasses()->attach($this->class->id, [
            'enrollment_date' => now()->subMonths(3),
            'status' => 'active',
        ]);
    }

    /**
     * Test report card generation
     */
    public function test_report_card_generation()
    {
        // Create grades for the student
        foreach ($this->subjects as $subject) {
            Grade::factory()->count(3)->create([
                'student_id' => $this->student->id,
                'subject_id' => $subject->id,
                'class_id' => $this->class->id,
                'teacher_id' => $this->teacher->id,
                'term' => 'Term 1',
                'value' => rand(70, 100),
                'max_value' => 100,
                'weight' => rand(1, 3),
            ]);
        }
        
        // Create some absences
        Absence::factory()->count(5)->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'is_justified' => false,
        ]);
        
        Absence::factory()->count(3)->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'is_justified' => true,
        ]);
        
        // Generate report card
        $reportCard = ReportCard::generate(
            $this->student->id,
            $this->class->id,
            'Term 1',
            '2024-2025'
        );
        
        $this->assertNotNull($reportCard);
        $this->assertEquals($this->student->id, $reportCard->student_id);
        $this->assertEquals($this->class->id, $reportCard->class_id);
        $this->assertEquals('Term 1', $reportCard->term);
        $this->assertEquals('2024-2025', $reportCard->academic_year);
        $this->assertEquals(8, $reportCard->total_absences);
        $this->assertEquals(3, $reportCard->justified_absences);
        $this->assertNotNull($reportCard->overall_average);
        $this->assertIsArray($reportCard->subject_grades);
    }

    /**
     * Test overall average calculation
     */
    public function test_overall_average_calculation()
    {
        // Create controlled grades
        $subject1 = $this->subjects[0];
        $subject2 = $this->subjects[1];
        
        // Subject 1: average should be 80
        Grade::factory()->create([
            'student_id' => $this->student->id,
            'subject_id' => $subject1->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'term' => 'Term 1',
            'value' => 80,
            'max_value' => 100,
            'weight' => 1,
        ]);
        
        // Subject 2: average should be 90
        Grade::factory()->create([
            'student_id' => $this->student->id,
            'subject_id' => $subject2->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'term' => 'Term 1',
            'value' => 90,
            'max_value' => 100,
            'weight' => 1,
        ]);
        
        $reportCard = ReportCard::generate(
            $this->student->id,
            $this->class->id,
            'Term 1',
            '2024-2025'
        );
        
        // Overall average should consider subject credits
        $this->assertGreaterThan(0, $reportCard->overall_average);
        $this->assertLessThanOrEqual(100, $reportCard->overall_average);
    }

    /**
     * Test conduct grade assignment
     */
    public function test_conduct_grade_assignment()
    {
        // Create excellent performance
        foreach ($this->subjects as $subject) {
            Grade::factory()->create([
                'student_id' => $this->student->id,
                'subject_id' => $subject->id,
                'class_id' => $this->class->id,
                'teacher_id' => $this->teacher->id,
                'term' => 'Term 1',
                'value' => rand(85, 100),
                'max_value' => 100,
            ]);
        }
        
        // Minimal absences
        Absence::factory()->count(2)->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'is_justified' => true,
        ]);
        
        $reportCard = ReportCard::generate(
            $this->student->id,
            $this->class->id,
            'Term 1',
            '2024-2025'
        );
        
        $this->assertContains($reportCard->conduct_grade, ['Excellent', 'Very Good']);
    }

    /**
     * Test student can view report cards
     */
    public function test_student_can_view_report_cards()
    {
        $reportCard = ReportCard::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
        ]);
        
        $response = $this->actingAs($this->student)
            ->get(route('student.report-cards.index'));
        
        $response->assertStatus(200);
        $response->assertViewHas('reportCards');
    }

    /**
     * Test student can view specific report card
     */
    public function test_student_can_view_specific_report_card()
    {
        $reportCard = ReportCard::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'subject_grades' => [
                ['subject_name' => 'Math', 'average' => 85],
                ['subject_name' => 'Science', 'average' => 90],
            ],
        ]);
        
        $response = $this->actingAs($this->student)
            ->get(route('student.report-cards.show', $reportCard));
        
        $response->assertStatus(200);
        $response->assertViewHas('reportCard');
    }

    /**
     * Test parent can view child's report card
     */
    public function test_parent_can_view_child_report_card()
    {
        $parent = User::factory()->create(['role' => 'parent']);
        
        $parent->parentChildren()->attach($this->student->id, [
            'relationship' => 'mother',
            'is_primary_contact' => true,
        ]);
        
        $reportCard = ReportCard::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
        ]);
        
        $response = $this->actingAs($parent)
            ->get(route('parent.children.report-card', [$this->student, $reportCard]));
        
        $response->assertStatus(200);
        $response->assertViewHas('reportCard');
    }

    /**
     * Test performance label assignment
     */
    public function test_performance_label_assignment()
    {
        $labels = [
            95 => 'Outstanding',
            85 => 'Excellent',
            75 => 'Good',
            65 => 'Satisfactory',
            55 => 'Pass',
            45 => 'Fail',
        ];
        
        foreach ($labels as $average => $expectedLabel) {
            $reportCard = ReportCard::factory()->create([
                'overall_average' => $average,
            ]);
            
            $this->assertEquals($expectedLabel, $reportCard->getPerformanceLabel());
        }
    }

    /**
     * Test report card uniqueness constraint
     */
    public function test_report_card_uniqueness_constraint()
    {
        // Create first report card
        $reportCard1 = ReportCard::generate(
            $this->student->id,
            $this->class->id,
            'Term 1',
            '2024-2025'
        );
        
        // Try to generate again with same parameters
        $reportCard2 = ReportCard::generate(
            $this->student->id,
            $this->class->id,
            'Term 1',
            '2024-2025'
        );
        
        // Should update existing, not create new
        $count = ReportCard::where('student_id', $this->student->id)
            ->where('class_id', $this->class->id)
            ->where('term', 'Term 1')
            ->where('academic_year', '2024-2025')
            ->count();
        
        $this->assertEquals(1, $count);
        $this->assertEquals($reportCard1->id, $reportCard2->id);
    }

    /**
     * Test report card finalization
     */
    public function test_report_card_finalization()
    {
        $reportCard = ReportCard::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'is_final' => false,
        ]);
        
        $this->assertFalse($reportCard->is_final);
        
        // Finalize report card
        $reportCard->update(['is_final' => true]);
        
        $this->assertTrue($reportCard->fresh()->is_final);
    }

    /**
     * Test report card download
     */
    public function test_report_card_download()
    {
        $reportCard = ReportCard::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
        ]);
        
        $response = $this->actingAs($this->student)
            ->get(route('student.report-cards.download', $reportCard));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
