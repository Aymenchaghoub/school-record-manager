<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Grade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeManagementTest extends TestCase
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
            'teacher_id' => $this->teacher->id,
        ]);
        
        // Create subject
        $this->subject = Subject::factory()->create();
        
        // Assign teacher to subject in class
        $this->class->subjects()->attach($this->subject->id, [
            'teacher_id' => $this->teacher->id,
            'hours_per_week' => 3,
            'room' => 'Room 101',
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
     * Test teacher can view grades page
     */
    public function test_teacher_can_view_grades_page()
    {
        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.grades.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('teacher.grades.index');
    }

    /**
     * Test teacher can create a grade
     */
    public function test_teacher_can_create_grade()
    {
        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.grades.store'), [
                'student_id' => $this->student->id,
                'subject_id' => $this->subject->id,
                'class_id' => $this->class->id,
                'value' => 17,
                'max_value' => 20,
                'type' => 'exam',
                'title' => 'Mid-term Exam',
                'grade_date' => now()->format('Y-m-d'),
                'term' => 'Term 1',
                'weight' => 2,
                'comment' => 'Good performance',
            ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('grades', [
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'value' => 17,
            'title' => 'Mid-term Exam',
        ]);
    }

    /**
     * Test teacher can update a grade
     */
    public function test_teacher_can_update_grade()
    {
        $grade = Grade::factory()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'value' => 15,
        ]);
        
        $response = $this->actingAs($this->teacher)
            ->put(route('teacher.grades.update', $grade), [
                'value' => 17,
                'comment' => 'Updated grade',
            ]);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('grades', [
            'id' => $grade->id,
            'value' => 17,
            'comment' => 'Updated grade',
        ]);
    }

    /**
     * Test teacher can delete a grade
     */
    public function test_teacher_can_delete_grade()
    {
        $grade = Grade::factory()->create([
            'teacher_id' => $this->teacher->id,
        ]);
        
        $response = $this->actingAs($this->teacher)
            ->delete(route('teacher.grades.destroy', $grade));
        
        $response->assertRedirect();
        
        $this->assertDatabaseMissing('grades', [
            'id' => $grade->id,
        ]);
    }

    /**
     * Test teacher cannot modify another teacher's grades
     */
    public function test_teacher_cannot_modify_another_teachers_grades()
    {
        $anotherTeacher = User::factory()->create(['role' => 'teacher']);
        
        $grade = Grade::factory()->create([
            'teacher_id' => $anotherTeacher->id,
        ]);
        
        $response = $this->actingAs($this->teacher)
            ->put(route('teacher.grades.update', $grade), [
                'value' => 18,
            ]);
        
        $response->assertStatus(403);
    }

    /**
     * Test batch grade entry
     */
    public function test_batch_grade_entry()
    {
        $students = User::factory()->count(3)->create(['role' => 'student']);
        
        foreach ($students as $student) {
            $student->studentClasses()->attach($this->class->id, [
                'enrollment_date' => now(),
                'status' => 'active',
            ]);
        }
        
        $gradeData = [];
        foreach ($students as $student) {
            $gradeData['grades'][] = [
                'student_id' => $student->id,
                'value' => rand(10, 20),
                'max_value' => 20,
            ];
        }
        
        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.grades.batch'), [
                'subject_id' => $this->subject->id,
                'class_id' => $this->class->id,
                'type' => 'quiz',
                'title' => 'Pop Quiz',
                'grade_date' => now()->format('Y-m-d'),
                'term' => 'Term 1',
                'weight' => 1,
                'grades' => $gradeData['grades'],
            ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        foreach ($students as $student) {
            $this->assertDatabaseHas('grades', [
                'student_id' => $student->id,
                'subject_id' => $this->subject->id,
                'title' => 'Pop Quiz',
            ]);
        }
    }

    /**
     * Test student can view their own grades
     */
    public function test_student_can_view_own_grades()
    {
        Grade::factory()->count(5)->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
        ]);
        
        $response = $this->actingAs($this->student)
            ->get(route('student.grades.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('student.grades.index');
        $response->assertViewHas('grades');
    }

    /**
     * Test student cannot view other students' grades
     */
    public function test_student_cannot_view_other_students_grades()
    {
        $anotherStudent = User::factory()->create(['role' => 'student']);
        
        Grade::factory()->create([
            'student_id' => $anotherStudent->id,
        ]);
        
        $response = $this->actingAs($this->student)
            ->get(route('student.grades.index'));
        
        $grades = $response->viewData('grades');
        
        // Ensure no grades belonging to the other student are visible
        $otherStudentGrades = collect($grades)->where('student_id', $anotherStudent->id);
        $this->assertCount(0, $otherStudentGrades, 'Student should not see other students\' grades');
    }

    /**
     * Test grade percentage calculation
     */
    public function test_grade_percentage_calculation()
    {
        $grade = Grade::factory()->create([
            'value' => 17,
            'max_value' => 20,
        ]);
        
        $this->assertEquals(85, $grade->getPercentage());
        
        $grade2 = Grade::factory()->create([
            'value' => 10,
            'max_value' => 20,
        ]);
        
        $this->assertEquals(50, $grade2->getPercentage());
    }

    /**
     * Test letter grade assignment
     */
    public function test_letter_grade_assignment()
    {
        $gradeValues = [
            ['value' => 19.0, 'letter' => 'A+'],
            ['value' => 17.4, 'letter' => 'A'],
            ['value' => 16.4, 'letter' => 'A-'],
            ['value' => 15.6, 'letter' => 'B+'],
            ['value' => 15.0, 'letter' => 'B'],
            ['value' => 14.2, 'letter' => 'B-'],
            ['value' => 13.6, 'letter' => 'C+'],
            ['value' => 12.8, 'letter' => 'C'],
            ['value' => 12.2, 'letter' => 'C-'],
            ['value' => 11.6, 'letter' => 'D+'],
            ['value' => 10.8, 'letter' => 'D'],
            ['value' => 10.2, 'letter' => 'D-'],
            ['value' => 9.0, 'letter' => 'F'],
        ];

        foreach ($gradeValues as $gradeData) {
            $grade = Grade::factory()->create([
                'value' => $gradeData['value'],
                'max_value' => 20,
            ]);
            
            $this->assertEquals($gradeData['letter'], $grade->getLetterGrade());
        }
    }

    /**
     * Test grade validation rules
     */
    public function test_grade_validation_rules()
    {
        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.grades.store'), [
                'student_id' => $this->student->id,
                'subject_id' => $this->subject->id,
                'class_id' => $this->class->id,
                'value' => 25, // Invalid: exceeds /20 scale
                'max_value' => 20,
                'type' => 'invalid_type', // Invalid type
                'grade_date' => 'not-a-date', // Invalid date
            ]);
        
        $response->assertSessionHasErrors(['value', 'type', 'grade_date']);
    }
}
