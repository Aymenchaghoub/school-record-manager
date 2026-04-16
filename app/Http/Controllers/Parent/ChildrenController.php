<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Grade;
use App\Models\Absence;
use App\Models\ReportCard;
use App\Services\GradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChildrenController extends Controller
{
    public function __construct(private readonly GradeService $gradeService)
    {
    }

    public function index()
    {
        $parent = Auth::user();
        $children = $parent->parentChildren()->with(['studentClasses' => function ($query) {
            $query->wherePivot('status', 'active');
        }])->get();

        $childIds = $children->pluck('id');

        $averageMap = Grade::query()
            ->whereIn('student_id', $childIds)
            ->selectRaw('student_id, AVG((value / NULLIF(max_value, 0)) * 100) as average')
            ->groupBy('student_id')
            ->pluck('average', 'student_id');

        $absenceMap = Absence::query()
            ->whereIn('student_id', $childIds)
            ->weekdaysOnly()
            ->selectRaw('student_id, COUNT(*) as total')
            ->groupBy('student_id')
            ->pluck('total', 'student_id');

        $recentGradesMap = Grade::query()
            ->whereIn('student_id', $childIds)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('student_id, COUNT(*) as total')
            ->groupBy('student_id')
            ->pluck('total', 'student_id');

        foreach ($children as $child) {
            $child->currentClass = $child->studentClasses->first();
            $child->overall_average = round((float) ($averageMap[$child->id] ?? 0), 2);
            $child->total_absences = (int) ($absenceMap[$child->id] ?? 0);
            $child->recent_grades_count = (int) ($recentGradesMap[$child->id] ?? 0);
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

        $gradeQuery = Grade::query()->where('student_id', $child->id);

        $grades = (clone $gradeQuery)
            ->with(['subject', 'class', 'teacher'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $statistics = [
            'average' => (clone $gradeQuery)->avg('value'),
            'highest' => (clone $gradeQuery)->max('value'),
            'lowest' => (clone $gradeQuery)->min('value'),
            'total_subjects' => (clone $gradeQuery)->distinct('subject_id')->count('subject_id')
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

        $absenceQuery = Absence::query()->where('student_id', $child->id);

        $absences = (clone $absenceQuery)
            ->with(['class'])
            ->orderBy('absence_date', 'desc')
            ->paginate(20);

        $statistics = [
            'total_absences' => (clone $absenceQuery)->count(),
            'justified' => (clone $absenceQuery)->where('is_justified', true)->count(),
            'unjustified' => (clone $absenceQuery)->where('is_justified', false)->count(),
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
        return $this->gradeService->overallAverageForStudent((int) $studentId);
    }
}
