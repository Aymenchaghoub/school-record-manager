<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\User;
use App\Models\ClassModel;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class GradeService
{
    /**
     * Calculate student's GPA
     */
    public function calculateGPA(User $student, ?string $semester = null): float
    {
        $query = Grade::where('student_id', $student->id);
        
        if ($semester) {
            $query->where('semester', $semester);
        }
        
        $grades = $query->get();
        
        if ($grades->isEmpty()) {
            return 0.0;
        }
        
        $totalPoints = 0;
        $totalCredits = 0;
        
        foreach ($grades as $grade) {
            $credit = $grade->subject->credits ?? 1;
            $totalPoints += ($grade->grade * $credit);
            $totalCredits += $credit;
        }
        
        return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.0;
    }
    
    /**
     * Get grade statistics for a class
     */
    public function getClassStatistics(ClassModel $class, ?Subject $subject = null): array
    {
        $query = Grade::where('class_id', $class->id);
        
        if ($subject) {
            $query->where('subject_id', $subject->id);
        }
        
        return [
            'average' => round($query->avg('value') ?? 0, 2),
            'highest' => $query->max('value') ?? 0,
            'lowest' => $query->min('value') ?? 0,
            'total_students' => $query->distinct('student_id')->count(),
            'pass_rate' => $this->calculatePassRate($query->get())
        ];
    }
    
    /**
     * Calculate pass rate (percentage of grades >= 50)
     */
    private function calculatePassRate($grades): float
    {
        if ($grades->isEmpty()) {
            return 0.0;
        }
        
        $passing = $grades->filter(fn($grade) => $grade->grade >= 50)->count();
        return round(($passing / $grades->count()) * 100, 2);
    }
    
    /**
     * Batch create grades for multiple students
     */
    public function batchCreate(array $data): bool
    {
        DB::beginTransaction();
        
        try {
            foreach ($data['grades'] as $gradeData) {
                Grade::create([
                    'student_id' => $gradeData['student_id'],
                    'subject_id' => $data['subject_id'],
                    'class_id' => $data['class_id'],
                    'teacher_id' => $data['teacher_id'],
                    'grade' => $gradeData['grade'],
                    'exam_type' => $data['exam_type'],
                    'semester' => $data['semester'] ?? 1,
                    'academic_year' => $data['academic_year'] ?? date('Y'),
                    'comment' => $gradeData['comment'] ?? null,
                ]);
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
