<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Grade;
use App\Models\Absence;
use App\Models\ReportCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChildrenController extends Controller
{
    public function index()
    {
        $parent = Auth::user();
        $children = $parent->parentChildren()->get();

        // Get additional data for each child
        foreach ($children as $child) {
            $child->currentClass = $child->studentClass();
            $child->overall_average = $this->calculateStudentAverage($child->id);
            $child->total_absences = Absence::where('student_id', $child->id)->weekdaysOnly()->count();
            $child->recent_grades_count = Grade::where('student_id', $child->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->count();
        }

        return view('parent.children.index', compact('children'));
    }

    public function show(User $child)
    {
        $parent = Auth::user();
        
        // Ensure this is the parent's child
        if (!$this->isParentOfChild($parent, $child)) {
            abort(403, 'Unauthorized access.');
        }

        $child->load(['studentClasses.class', 'grades' => function($query) {
            $query->latest()->limit(5);
        }, 'absences' => function($query) {
            $query->latest()->limit(5);
        }]);

        $statistics = [
            'average_grade' => Grade::where('student_id', $child->id)->avg('value'),
            'total_absences' => Absence::where('student_id', $child->id)->count(),
            'unjustified_absences' => Absence::where('student_id', $child->id)
                ->where('is_justified', false)->count(),
        ];

        return view('parent.children.show', compact('child', 'statistics'));
    }

    public function grades(User $child)
    {
        $parent = Auth::user();
        
        // Ensure this is the parent's child
        if (!$this->isParentOfChild($parent, $child)) {
            abort(403, 'Unauthorized access.');
        }

        $grades = Grade::with(['subject', 'class', 'teacher'])
            ->where('student_id', $child->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $statistics = [
            'average' => $grades->avg('value'),
            'highest' => $grades->max('value'),
            'lowest' => $grades->min('value'),
            'total_subjects' => $grades->pluck('subject_id')->unique()->count()
        ];

        return view('parent.children.grades', compact('child', 'grades', 'statistics'));
    }

    public function absences(User $child)
    {
        $parent = Auth::user();
        
        // Ensure this is the parent's child
        if (!$this->isParentOfChild($parent, $child)) {
            abort(403, 'Unauthorized access.');
        }

        $absences = Absence::with(['class'])
            ->where('student_id', $child->id)
            ->orderBy('absence_date', 'desc')
            ->paginate(20);

        $statistics = [
            'total_absences' => Absence::where('student_id', $child->id)->count(),
            'justified' => Absence::where('student_id', $child->id)->where('is_justified', true)->count(),
            'unjustified' => Absence::where('student_id', $child->id)->where('is_justified', false)->count(),
        ];

        return view('parent.children.absences', compact('child', 'absences', 'statistics'));
    }

    public function reportCards(User $child)
    {
        $parent = Auth::user();
        
        // Ensure this is the parent's child
        if (!$this->isParentOfChild($parent, $child)) {
            abort(403, 'Unauthorized access.');
        }

        $reportCards = ReportCard::where('student_id', $child->id)
            ->orderBy('academic_year', 'desc')
            ->orderBy('term', 'desc')
            ->paginate(10);

        return view('parent.children.report-cards', compact('child', 'reportCards'));
    }

    public function viewReportCard(User $child, ReportCard $reportCard)
    {
        $parent = Auth::user();
        
        // Ensure this is the parent's child and the report card belongs to the child
        if (!$this->isParentOfChild($parent, $child) || $reportCard->student_id !== $child->id) {
            abort(403, 'Unauthorized access.');
        }

        $reportCard->load(['student', 'class', 'preparedBy']);

        return view('parent.children.report-card', compact('child', 'reportCard'));
    }

    private function isParentOfChild(User $parent, User $child)
    {
        if ($child->role !== 'student') {
            return false;
        }

        return DB::table('parent_students')
            ->where('parent_id', $parent->id)
            ->where('student_id', $child->id)
            ->exists();
    }

    private function calculateStudentAverage($studentId)
    {
        $grades = Grade::where('student_id', $studentId)->get();
        
        if ($grades->isEmpty()) {
            return 0;
        }

        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($grades as $grade) {
            $normalizedScore = ($grade->value / $grade->max_value) * 100;
            $totalWeightedScore += $normalizedScore * $grade->weight;
            $totalWeight += $grade->weight;
        }

        return $totalWeight > 0 ? round($totalWeightedScore / $totalWeight, 2) : 0;
    }
}
