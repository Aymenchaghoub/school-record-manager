<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'term',
        'academic_year',
        'overall_average',
        'total_absences',
        'justified_absences',
        'rank_in_class',
        'total_students',
        'subject_grades',
        'principal_remarks',
        'teacher_remarks',
        'conduct_grade',
        'issue_date',
        'is_final',
    ];

    protected $casts = [
        'overall_average' => 'decimal:2',
        'total_absences' => 'integer',
        'justified_absences' => 'integer',
        'rank_in_class' => 'integer',
        'total_students' => 'integer',
        'subject_grades' => 'json',
        'issue_date' => 'date',
        'is_final' => 'boolean',
    ];

    /**
     * Get the student for the report card
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the class for the report card
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Generate report card for a student
     */
    public static function generate($studentId, $classId, $term, $academicYear)
    {
        $student = User::find($studentId);
        $class = ClassModel::find($classId);
        
        if (!$student || !$class) {
            return null;
        }

        // Calculate subject grades
        $subjectGrades = [];
        $subjects = $class->subjects;
        $totalWeightedAverage = 0;
        $totalCredits = 0;

        foreach ($subjects as $subject) {
            $grades = Grade::where('student_id', $studentId)
                ->where('subject_id', $subject->id)
                ->where('class_id', $classId)
                ->where('term', $term)
                ->get();

            if ($grades->isEmpty()) {
                continue;
            }

            $totalWeightedScore = 0;
            $totalWeight = 0;

            foreach ($grades as $grade) {
                $normalizedScore = ($grade->value / $grade->max_value) * 100;
                $totalWeightedScore += $normalizedScore * $grade->weight;
                $totalWeight += $grade->weight;
            }

            $subjectAverage = $totalWeight > 0 ? round($totalWeightedScore / $totalWeight, 2) : 0;

            $subjectGrades[] = [
                'subject_id' => $subject->id,
                'subject_name' => $subject->name,
                'subject_code' => $subject->code,
                'credits' => $subject->credits,
                'average' => $subjectAverage,
                'grades_count' => $grades->count(),
            ];

            $totalWeightedAverage += $subjectAverage * $subject->credits;
            $totalCredits += $subject->credits;
        }

        $overallAverage = $totalCredits > 0 ? round($totalWeightedAverage / $totalCredits, 2) : 0;

        // Calculate absences
        $absences = Absence::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->whereYear('absence_date', substr($academicYear, 0, 4))
            ->get();

        $totalAbsences = $absences->count();
        $justifiedAbsences = $absences->where('is_justified', true)->count();

        // Calculate rank (simplified - in production, this would be more complex)
        $allStudentAverages = [];
        foreach ($class->students as $classStudent) {
            $studentReportCard = self::where('student_id', $classStudent->id)
                ->where('class_id', $classId)
                ->where('term', $term)
                ->where('academic_year', $academicYear)
                ->first();

            if ($studentReportCard) {
                $allStudentAverages[] = $studentReportCard->overall_average;
            }
        }

        rsort($allStudentAverages);
        $rank = array_search($overallAverage, $allStudentAverages) + 1;

        // Determine conduct grade based on absences and overall performance
        $conductGrade = 'Good';
        if ($totalAbsences - $justifiedAbsences <= 2 && $overallAverage >= 85) {
            $conductGrade = 'Excellent';
        } elseif ($totalAbsences - $justifiedAbsences <= 5 && $overallAverage >= 75) {
            $conductGrade = 'Very Good';
        } elseif ($totalAbsences - $justifiedAbsences > 10 || $overallAverage < 50) {
            $conductGrade = 'Fair';
        } elseif ($totalAbsences - $justifiedAbsences > 15 || $overallAverage < 40) {
            $conductGrade = 'Poor';
        }

        return self::updateOrCreate(
            [
                'student_id' => $studentId,
                'class_id' => $classId,
                'term' => $term,
                'academic_year' => $academicYear,
            ],
            [
                'overall_average' => $overallAverage,
                'total_absences' => $totalAbsences,
                'justified_absences' => $justifiedAbsences,
                'rank_in_class' => $rank,
                'total_students' => $class->students()->count(),
                'subject_grades' => $subjectGrades,
                'conduct_grade' => $conductGrade,
                'issue_date' => now(),
                'is_final' => false,
            ]
        );
    }

    /**
     * Get performance label
     */
    public function getPerformanceLabel()
    {
        if ($this->overall_average >= 90) return 'Outstanding';
        if ($this->overall_average >= 80) return 'Excellent';
        if ($this->overall_average >= 70) return 'Good';
        if ($this->overall_average >= 60) return 'Satisfactory';
        if ($this->overall_average >= 50) return 'Pass';
        return 'Fail';
    }

    /**
     * Get performance color
     */
    public function getPerformanceColor()
    {
        if ($this->overall_average >= 85) return 'text-green-600';
        if ($this->overall_average >= 70) return 'text-blue-600';
        if ($this->overall_average >= 60) return 'text-yellow-600';
        if ($this->overall_average >= 50) return 'text-orange-600';
        return 'text-red-600';
    }
}
