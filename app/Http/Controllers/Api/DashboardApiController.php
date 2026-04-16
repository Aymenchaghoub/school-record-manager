<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\ClassModel;
use App\Models\Event;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\Subject;
use App\Models\User;
use App\Services\GradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardApiController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly GradeService $gradeService)
    {
    }

    public function admin(Request $request): JsonResponse
    {
        $stats = [
            'total_students' => User::where('role', 'student')->where('is_active', true)->count(),
            'total_teachers' => User::where('role', 'teacher')->where('is_active', true)->count(),
            'total_parents' => User::where('role', 'parent')->where('is_active', true)->count(),
            'total_classes' => ClassModel::where('is_active', true)->count(),
            'total_subjects' => Subject::where('is_active', true)->count(),
            'upcoming_events' => Event::where('start_date', '>=', now())->count(),
        ];

        return $this->success([
            'stats' => $stats,
        ], 'Admin dashboard loaded successfully.');
    }

    public function teacher(Request $request): JsonResponse
    {
        $teacher = $request->user();
        $classIds = $teacher->teacherClasses()->pluck('classes.id')->unique();

        $stats = [
            'total_classes' => $classIds->count(),
            'total_subjects' => $teacher->teacherSubjects()->count(),
            'total_students' => $this->countStudentsInClasses($classIds),
            'grades_this_week' => Grade::where('teacher_id', $teacher->id)
                ->where('grade_date', '>=', now()->subWeek()->toDateString())
                ->count(),
            'absences_today' => Absence::where('recorded_by', $teacher->id)
                ->whereDate('absence_date', now()->toDateString())
                ->count(),
            'upcoming_events' => Event::where(function ($query) use ($teacher, $classIds) {
                $query->where('created_by', $teacher->id)
                    ->orWhereNull('class_id')
                    ->orWhereIn('class_id', $classIds);
            })->where('start_date', '>=', now())->count(),
        ];

        return $this->success([
            'stats' => $stats,
        ], 'Teacher dashboard loaded successfully.');
    }

    public function student(Request $request): JsonResponse
    {
        $student = $request->user();
        $currentClass = $student->studentClass();
        $overallAverage = $this->gradeService->overallAverageForStudent($student->id);
        $totalAbsences = Absence::where('student_id', $student->id)->count();

        $stats = [
            'gpa' => $overallAverage,
            'overall_average' => $overallAverage,
            'total_absences' => $totalAbsences,
            'attendance_rate' => round(max(0, (180 - $totalAbsences) / 180) * 100, 1),
            'total_subjects' => Grade::where('student_id', $student->id)
                ->distinct('subject_id')
                ->count('subject_id'),
            'class_rank' => $this->studentRankInClass($student->id, $currentClass?->id),
            'upcoming_events' => Event::where('start_date', '>=', now())
                ->where(function ($query) use ($currentClass) {
                    $query->whereNull('class_id');

                    if ($currentClass?->id) {
                        $query->orWhere('class_id', $currentClass->id);
                    }
                })
                ->count(),
        ];

        return $this->success([
            'stats' => $stats,
        ], 'Student dashboard loaded successfully.');
    }

    public function parent(Request $request): JsonResponse
    {
        $parent = $request->user();
        $children = $parent->parentChildren()->get();
        $childIds = $children->pluck('id');

        $classIds = $children
            ->map(fn (User $child) => $child->studentClass()?->id)
            ->filter()
            ->unique()
            ->values();

        $averageGrade = Grade::whereIn('student_id', $childIds)
            ->selectRaw('AVG((value / NULLIF(max_value, 0)) * 100) as average')
            ->value('average');

        $stats = [
            'total_children' => $childIds->count(),
            'average_grade' => $this->normalizeAverage($averageGrade),
            'total_absences' => Absence::whereIn('student_id', $childIds)->count(),
            'upcoming_events' => Event::where('start_date', '>=', now())
                ->where(function ($query) use ($classIds) {
                    $query->whereNull('class_id')
                        ->orWhereIn('class_id', $classIds);
                })
                ->count(),
        ];

        return $this->success([
            'stats' => $stats,
        ], 'Parent dashboard loaded successfully.');
    }

    private function averageForStudent(int $studentId): float
    {
        return $this->gradeService->overallAverageForStudent($studentId);
    }

    private function countStudentsInClasses(Collection $classIds): int
    {
        if ($classIds->isEmpty()) {
            return 0;
        }

        return User::whereIn('id', function ($query) use ($classIds) {
            $query->select('student_id')
                ->from('student_classes')
                ->whereIn('class_id', $classIds)
                ->where('status', 'active');
        })->count();
    }

    private function studentRankInClass(int $studentId, ?int $classId): ?int
    {
        if (! $classId) {
            return null;
        }

        $studentIds = ClassModel::find($classId)?->students()->pluck('users.id');

        if (! $studentIds || $studentIds->isEmpty()) {
            return null;
        }

        $averages = Grade::query()
            ->select('student_id')
            ->selectRaw('AVG((value / NULLIF(max_value, 0)) * 100) as average')
            ->whereIn('student_id', $studentIds)
            ->groupBy('student_id')
            ->orderByDesc('average')
            ->get();

        $index = $averages->search(fn ($row) => (int) $row->student_id === $studentId);

        return $index === false ? null : $index + 1;
    }

    private function normalizeAverage(mixed $average): float
    {
        return round((float) ($average ?? 0), 2);
    }
}
