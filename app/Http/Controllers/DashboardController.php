<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\Absence;
use App\Models\Event;
use App\Models\ReportCard;
use App\Services\GradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private readonly GradeService $gradeService)
    {
    }

    /**
     * Show the dashboard based on user role
     */
    public function index()
    {
        $user = Auth::user();
        
        return match($user->role) {
            'admin' => $this->adminDashboard(),
            'teacher' => $this->teacherDashboard(),
            'student' => $this->studentDashboard(),
            'parent' => $this->parentDashboard(),
            default => abort(403),
        };
    }

    /**
     * Admin dashboard
     */
    private function adminDashboard()
    {
        $stats = [
            'total_students' => User::where('role', 'student')->where('is_active', true)->count(),
            'total_teachers' => User::where('role', 'teacher')->where('is_active', true)->count(),
            'total_parents' => User::where('role', 'parent')->where('is_active', true)->count(),
            'total_classes' => ClassModel::where('is_active', true)->count(),
            'total_subjects' => Subject::where('is_active', true)->count(),
            'upcoming_events' => Event::upcoming()->count(),
        ];

        // Recent activities
        $recentGrades = Grade::with(['student', 'subject', 'teacher'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentAbsences = Absence::with(['student', 'class'])
            ->orderBy('absence_date', 'desc')
            ->limit(10)
            ->get();

        // Performance metrics (grouped to avoid N+1 lookups)
        $activeClasses = ClassModel::query()
            ->where('is_active', true)
            ->withCount('students')
            ->get();

        $classAverages = Grade::query()
            ->whereIn('class_id', $activeClasses->pluck('id'))
            ->selectRaw('class_id, AVG(value) as average_grade')
            ->groupBy('class_id')
            ->pluck('average_grade', 'class_id');

        $classPerformance = $activeClasses->map(function ($class) use ($classAverages) {
            return [
                'class' => $class,
                'average_grade' => round((float) ($classAverages[$class->id] ?? 0), 2),
                'student_count' => (int) $class->students_count,
            ];
        });

        // Absence trends (last 30 days)
        $absenceTrends = Absence::select(DB::raw('DATE(absence_date) as date'), DB::raw('COUNT(*) as count'))
            ->where('absence_date', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $upcomingEvents = Event::upcoming()->limit(5)->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentGrades',
            'recentAbsences',
            'classPerformance',
            'absenceTrends',
            'upcomingEvents'
        ));
    }

    /**
     * Teacher dashboard
     */
    private function teacherDashboard()
    {
        $teacher = Auth::user();
        
        $stats = [
            'total_classes' => $teacher->teacherClasses()->count(),
            'total_subjects' => $teacher->teacherSubjects()->count(),
            'total_students' => User::whereIn('id', function ($query) use ($teacher) {
                $query->select('student_id')
                    ->from('student_classes')
                    ->whereIn('class_id', $teacher->teacherClasses()->pluck('classes.id'));
            })->count(),
            'grades_this_week' => $teacher->recordedGrades()->recent(7)->count(),
            'absences_today' => Absence::where('recorded_by', $teacher->id)
                ->whereDate('absence_date', today())
                ->count(),
        ];

        $myClasses = $teacher->teacherClasses()
            ->with(['students', 'subjects'])
            ->get();

        $recentGrades = $teacher->recordedGrades()
            ->with(['student', 'subject', 'class'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $todaysSchedule = $teacher->teacherClasses()
            ->with('subjects')
            ->get()
            ->map(function ($class) use ($teacher) {
                $subjects = $class->subjects()->wherePivot('teacher_id', $teacher->id)->get();
                return [
                    'class' => $class,
                    'subjects' => $subjects,
                ];
            });

        $upcomingEvents = Event::where(function ($query) use ($teacher) {
            $query->whereNull('class_id')
                ->orWhereIn('class_id', $teacher->teacherClasses()->pluck('classes.id'));
        })
        ->upcoming()
        ->limit(5)
        ->get();

        $absenceCounts = Absence::query()
            ->whereIn('class_id', $myClasses->pluck('id'))
            ->whereDate('absence_date', today())
            ->selectRaw('class_id, COUNT(*) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $classesWithHighAbsences = $myClasses
            ->map(function ($class) use ($absenceCounts) {
                return [
                    'class' => $class,
                    'absence_count' => (int) ($absenceCounts[$class->id] ?? 0),
                ];
            })
            ->filter(fn (array $item) => $item['absence_count'] > 3)
            ->values()
            ->all();

        return view('teacher.dashboard', compact(
            'stats',
            'myClasses',
            'recentGrades',
            'todaysSchedule',
            'upcomingEvents',
            'classesWithHighAbsences'
        ));
    }

    /**
     * Student dashboard
     */
    private function studentDashboard()
    {
        $student = Auth::user();
        $currentClass = $student->studentClass();
        
        if (!$currentClass) {
            return view('student.no-class');
        }

        $totalAbsences = $student->studentAbsences()->weekdaysOnly()->count();
        $justifiedAbsences = $student->studentAbsences()->weekdaysOnly()->justified()->count();
        $totalDays = 180; // Typical school year days
        $attendanceRate = $totalDays > 0 ? round((($totalDays - $totalAbsences) / $totalDays) * 100, 1) : 0;
        
        // Get total subjects from grades or class subjects
        $totalSubjects = $student->studentGrades()
            ->distinct('subject_id')
            ->count('subject_id');
        
        if ($totalSubjects == 0 && $currentClass) {
            $totalSubjects = $currentClass->subjects()->count();
        }
        
        // Calculate class rank
        $classStudents = $currentClass->students()->get();
        $studentAverages = Grade::query()
            ->whereIn('student_id', $classStudents->pluck('id'))
            ->selectRaw('student_id, AVG((value / NULLIF(max_value, 0)) * 100) as average')
            ->groupBy('student_id')
            ->pluck('average', 'student_id')
            ->map(fn ($value) => round((float) $value, 2))
            ->all();

        foreach ($classStudents as $classStudent) {
            $studentAverages[$classStudent->id] = $studentAverages[$classStudent->id] ?? 0;
        }

        arsort($studentAverages); // Sort descending by average
        $rank = array_search($student->id, array_keys($studentAverages)) + 1;
        $totalStudents = count($studentAverages);
        
        $stats = [
            'gpa' => $this->calculateStudentAverage($student->id),
            'overall_average' => $this->calculateStudentAverage($student->id),
            'total_absences' => $totalAbsences,
            'justified_absences' => $justifiedAbsences,
            'attendance_rate' => $attendanceRate,
            'total_subjects' => $totalSubjects,
            'class_rank' => $rank && $totalStudents > 0 ? "#$rank of $totalStudents" : 'N/A',
            'gpa_change' => '+0.0',
            'gpa_trend' => 'neutral',
            'upcoming_exams' => Event::forClass($currentClass->id)
                ->byType('exam')
                ->upcoming()
                ->count(),
        ];

        $recentGrades = $student->studentGrades()
            ->with(['subject', 'teacher'])
            ->orderBy('grade_date', 'desc')
            ->limit(10)
            ->get();

        $subjectAverageMap = Grade::query()
            ->where('student_id', $student->id)
            ->where('class_id', $currentClass->id)
            ->selectRaw('subject_id, AVG((value / NULLIF(max_value, 0)) * 100) as average')
            ->groupBy('subject_id')
            ->pluck('average', 'subject_id');

        $subjectAverages = $currentClass->subjects
            ->map(function ($subject) use ($subjectAverageMap) {
                if (! isset($subjectAverageMap[$subject->id])) {
                    return null;
                }

                return [
                    'subject' => $subject,
                    'average' => round((float) $subjectAverageMap[$subject->id], 2),
                ];
            })
            ->filter()
            ->values()
            ->all();

        $upcomingEvents = Event::where(function ($query) use ($currentClass) {
            $query->whereNull('class_id')
                ->orWhere('class_id', $currentClass->id);
        })
        ->upcoming()
        ->limit(5)
        ->get();

        $recentAbsences = $student->studentAbsences()
            ->with('class')
            ->weekdaysOnly() // Exclude weekend absences
            ->orderBy('absence_date', 'desc')
            ->limit(5)
            ->get();

        $latestReportCard = $student->studentReportCards()
            ->where('class_id', $currentClass->id)
            ->orderBy('issue_date', 'desc')
            ->first();

        return view('student.dashboard', compact(
            'stats',
            'currentClass',
            'recentGrades',
            'subjectAverages',
            'upcomingEvents',
            'recentAbsences',
            'latestReportCard'
        ))->with('subjectPerformance', $subjectAverages);
    }

    /**
     * Parent dashboard
     */
    private function parentDashboard()
    {
        $parent = Auth::user();
        $children = $parent->parentChildren()->get();

        $childrenStats = [];
        foreach ($children as $child) {
            $currentClass = $child->studentClass();
            
            // Get recent grades for this child
            $recentGrades = Grade::where('student_id', $child->id)
                ->with(['subject', 'class', 'teacher'])
                ->orderBy('grade_date', 'desc')
                ->limit(5)
                ->get();
            
            $childrenStats[] = [
                'child' => $child,
                'class' => $currentClass,
                'overall_average' => $this->calculateStudentAverage($child->id),
                'total_absences' => Absence::where('student_id', $child->id)->weekdaysOnly()->count(),
                'recent_grades' => $recentGrades,
                'latest_report_card' => ReportCard::where('student_id', $child->id)
                    ->orderBy('issue_date', 'desc')
                    ->first(),
            ];
        }

        $upcomingEvents = Event::where(function ($query) use ($children) {
            $classIds = $children->map(function ($child) {
                $class = $child->studentClass();
                return $class ? $class->id : null;
            })->filter();
            
            $query->whereNull('class_id')
                ->orWhereIn('class_id', $classIds);
        })
        ->upcoming()
        ->limit(10)
        ->get();

        return view('parent.dashboard', compact('childrenStats', 'upcomingEvents'));
    }

    /**
     * Calculate student's overall average
     */
    private function calculateStudentAverage($studentId)
    {
        return $this->gradeService->overallAverageForStudent((int) $studentId);
    }
}
