<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Absence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsenceManagementTest extends TestCase
{
    use RefreshDatabase;

    private $teacher;
    private $student;
    private $class;
    private $subject;

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
        ]);
        
        // Create subject
        $this->subject = Subject::factory()->create();
        
        // Assign teacher to subject in class
        $this->class->subjects()->attach($this->subject->id, [
            'teacher_id' => $this->teacher->id,
        ]);
        
        // Create student and enroll in class
        $this->student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);
        
        $this->student->studentClasses()->attach($this->class->id, [
            'enrollment_date' => now(),
            'status' => 'active',
        ]);
    }

    /**
     * Test teacher can record absence
     */
    public function test_teacher_can_record_absence()
    {
        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.absences.store'), [
                'student_id' => $this->student->id,
                'class_id' => $this->class->id,
                'subject_id' => $this->subject->id,
                'absence_date' => now()->format('Y-m-d'),
                'type' => 'full_day',
                'reason' => 'Sick',
                'is_justified' => false,
            ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('absences', [
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'recorded_by' => $this->teacher->id,
            'reason' => 'Sick',
        ]);
    }

    /**
     * Test batch absence recording
     */
    public function test_batch_absence_recording()
    {
        $students = User::factory()->count(3)->create(['role' => 'student']);
        
        foreach ($students as $student) {
            $student->studentClasses()->attach($this->class->id, [
                'enrollment_date' => now(),
                'status' => 'active',
            ]);
        }
        
        $absenceData = [];
        foreach ($students as $student) {
            $absenceData[] = $student->id;
        }
        
        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.absences.batch'), [
                'class_id' => $this->class->id,
                'subject_id' => $this->subject->id,
                'absence_date' => now()->format('Y-m-d'),
                'type' => 'full_day',
                'absent_students' => $absenceData,
            ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        foreach ($students as $student) {
            $this->assertDatabaseHas('absences', [
                'student_id' => $student->id,
                'class_id' => $this->class->id,
                'absence_date' => now()->format('Y-m-d'),
            ]);
        }
    }

    /**
     * Test teacher can justify absence
     */
    public function test_teacher_can_justify_absence()
    {
        $absence = Absence::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'recorded_by' => $this->teacher->id,
            'is_justified' => false,
        ]);
        
        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.absences.justify', $absence), [
                'justification' => 'Medical certificate provided',
            ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('absences', [
            'id' => $absence->id,
            'is_justified' => true,
            'justification' => 'Medical certificate provided',
        ]);
    }

    /**
     * Test different absence types
     */
    public function test_different_absence_types()
    {
        $types = ['full_day', 'partial', 'late_arrival', 'early_departure'];
        
        foreach ($types as $type) {
            $response = $this->actingAs($this->teacher)
                ->post(route('teacher.absences.store'), [
                    'student_id' => $this->student->id,
                    'class_id' => $this->class->id,
                    'absence_date' => now()->format('Y-m-d'),
                    'type' => $type,
                    'start_time' => '09:00',
                    'end_time' => '10:00',
                ]);
            
            $response->assertRedirect();
            
            $this->assertDatabaseHas('absences', [
                'student_id' => $this->student->id,
                'type' => $type,
            ]);
        }
    }

    /**
     * Test student can view their absences
     */
    public function test_student_can_view_own_absences()
    {
        Absence::factory()->count(5)->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
        ]);
        
        $response = $this->actingAs($this->student)
            ->get(route('student.absences'));
        
        $response->assertStatus(200);
        $response->assertViewHas('absences');
    }

    /**
     * Test parent can view child's absences
     */
    public function test_parent_can_view_child_absences()
    {
        $parent = User::factory()->create(['role' => 'parent']);
        
        $parent->parentChildren()->attach($this->student->id, [
            'relationship' => 'father',
            'is_primary_contact' => true,
        ]);
        
        Absence::factory()->count(3)->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
        ]);
        
        $response = $this->actingAs($parent)
            ->get(route('parent.children.absences', $this->student));
        
        $response->assertStatus(200);
        $response->assertViewHas('absences');
    }

    /**
     * Test absence statistics calculation
     */
    public function test_absence_statistics_calculation()
    {
        // Create justified absences
        Absence::factory()->count(3)->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'is_justified' => true,
        ]);
        
        // Create unjustified absences
        Absence::factory()->count(2)->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'is_justified' => false,
        ]);
        
        $totalAbsences = $this->student->studentAbsences()->count();
        $justifiedAbsences = $this->student->studentAbsences()->justified()->count();
        $unjustifiedAbsences = $this->student->studentAbsences()->unjustified()->count();
        
        $this->assertEquals(5, $totalAbsences);
        $this->assertEquals(3, $justifiedAbsences);
        $this->assertEquals(2, $unjustifiedAbsences);
    }

    /**
     * Test absence duration calculation
     */
    public function test_absence_duration_calculation()
    {
        $absence = Absence::factory()->create([
            'type' => 'partial',
            'start_time' => '09:00:00',
            'end_time' => '11:30:00',
        ]);
        
        $this->assertEquals(2.5, $absence->getDurationInHours());
        
        $fullDayAbsence = Absence::factory()->create([
            'type' => 'full_day',
        ]);
        
        $this->assertEquals(8, $fullDayAbsence->getDurationInHours());
    }

    /**
     * Test absence justification time limit
     */
    public function test_absence_justification_time_limit()
    {
        // Recent absence (can be justified)
        $recentAbsence = Absence::factory()->create([
            'absence_date' => now()->subDays(2),
        ]);
        
        $this->assertTrue($recentAbsence->canBeJustified(3));
        
        // Old absence (cannot be justified)
        $oldAbsence = Absence::factory()->create([
            'absence_date' => now()->subDays(5),
        ]);
        
        $this->assertFalse($oldAbsence->canBeJustified(3));
    }

    /**
     * Test absence filtering by date range
     */
    public function test_absence_filtering_by_date_range()
    {
        // Create absences in different date ranges
        Absence::factory()->create([
            'student_id' => $this->student->id,
            'absence_date' => now()->subDays(10),
        ]);
        
        Absence::factory()->create([
            'student_id' => $this->student->id,
            'absence_date' => now()->subDays(5),
        ]);
        
        Absence::factory()->create([
            'student_id' => $this->student->id,
            'absence_date' => now(),
        ]);
        
        $lastWeekAbsences = Absence::where('student_id', $this->student->id)
            ->dateBetween(now()->subDays(7), now())
            ->count();
        
        $this->assertEquals(2, $lastWeekAbsences);
    }

    /**
     * Test current month absences scope
     */
    public function test_current_month_absences_scope()
    {
        // Create absence in current month
        Absence::factory()->create([
            'student_id' => $this->student->id,
            'absence_date' => now(),
        ]);
        
        // Create absence in previous month
        Absence::factory()->create([
            'student_id' => $this->student->id,
            'absence_date' => now()->subMonth(),
        ]);
        
        $currentMonthAbsences = Absence::where('student_id', $this->student->id)
            ->currentMonth()
            ->count();
        
        $this->assertEquals(1, $currentMonthAbsences);
    }
}
