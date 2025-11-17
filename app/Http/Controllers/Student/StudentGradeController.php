<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\Absence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentGradeController extends Controller
{
    public function index()
    {
        $student = Auth::user();
        $grades = Grade::with(['subject', 'class', 'teacher'])
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get statistics from ALL grades, not just paginated ones
        $allGrades = Grade::where('student_id', $student->id)->get();
        
        $statistics = [
            'average' => $allGrades->avg('value'),
            'highest' => $allGrades->max('value'),
            'lowest' => $allGrades->min('value'),
            'total_subjects' => $allGrades->pluck('subject_id')->unique()->count()
        ];

        return view('student.grades.index', compact('grades', 'statistics'));
    }

    public function bySubject(Subject $subject)
    {
        $student = Auth::user();
        $grades = Grade::with(['class', 'teacher'])
            ->where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $statistics = [
            'average' => $grades->avg('value'),
            'highest' => $grades->max('value'),
            'lowest' => $grades->min('value'),
            'exam_count' => $grades->count()
        ];

        return view('student.grades.by-subject', compact('grades', 'subject', 'statistics'));
    }

    public function absences()
    {
        $student = Auth::user();
        $absences = Absence::with(['class'])
            ->where('student_id', $student->id)
            ->weekdaysOnly() // Exclude weekend absences
            ->orderBy('absence_date', 'desc')
            ->paginate(20);

        $statistics = [
            'total_absences' => Absence::where('student_id', $student->id)->weekdaysOnly()->count(),
            'justified' => Absence::where('student_id', $student->id)->weekdaysOnly()->where('is_justified', true)->count(),
            'unjustified' => Absence::where('student_id', $student->id)->weekdaysOnly()->where('is_justified', false)->count(),
        ];

        return view('student.absences.index', compact('absences', 'statistics'));
    }
}
