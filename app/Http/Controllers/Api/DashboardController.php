<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\ClassModel;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function studentsPerClass(): JsonResponse
    {
        $classes = ClassModel::query()
            ->withCount('students')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'labels' => $classes->pluck('name')->values(),
            'data' => $classes->pluck('students_count')->map(fn ($count) => (int) $count)->values(),
        ]);
    }

    public function averagePerSubject(): JsonResponse
    {
        $averages = Grade::query()
            ->join('subjects', 'grades.subject_id', '=', 'subjects.id')
            ->select('subjects.name as subject_name')
            ->selectRaw('AVG(grades.value) as average_grade')
            ->groupBy('subjects.id', 'subjects.name')
            ->orderBy('subjects.name')
            ->get();

        return response()->json([
            'labels' => $averages->pluck('subject_name')->values(),
            'data' => $averages->map(fn ($row) => round((float) $row->average_grade, 1))->values(),
        ]);
    }

    public function absencesPerMonth(): JsonResponse
    {
        [$startDate, $endDate] = $this->schoolYearRange();

        $monthKeys = [];
        $labels = [];
        $cursor = $startDate->copy()->startOfMonth();

        while ($cursor->lte($endDate)) {
            $monthKeys[] = $cursor->format('Y-m');
            $labels[] = $cursor->format('M');
            $cursor->addMonth();
        }

        $monthExpression = $this->monthKeyExpression();

        $rows = Absence::query()
            ->whereBetween('absence_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw("{$monthExpression} as month_key")
            ->selectRaw('COUNT(*) as total')
            ->groupByRaw($monthExpression)
            ->get();

        $countMap = $rows->mapWithKeys(fn ($row) => [$row->month_key => (int) $row->total]);

        return response()->json([
            'labels' => $labels,
            'data' => collect($monthKeys)->map(fn ($key) => (int) ($countMap[$key] ?? 0))->values(),
        ]);
    }

    public function gradeEvolution(Request $request): JsonResponse
    {
        $user = $request->user();

        $requestedStudentId = (int) $request->input('student_id', 0);
        $studentId = $requestedStudentId;

        if ($user->isStudent()) {
            $studentId = (int) $user->id;
        } elseif ($user->isParent()) {
            $studentId = $this->resolveParentStudentId($user, $requestedStudentId);
        }

        if ($studentId <= 0) {
            return response()->json([
                'labels' => [],
                'data' => [],
            ]);
        }

        $grades = Grade::query()
            ->where('student_id', $studentId)
            ->orderBy('created_at')
            ->get(['created_at', 'value']);

        return response()->json([
            'labels' => $grades
                ->map(fn ($grade) => Carbon::parse($grade->created_at)->format('M j'))
                ->values(),
            'data' => $grades
                ->map(fn ($grade) => round((float) $grade->value, 1))
                ->values(),
        ]);
    }

    public function kpis(): JsonResponse
    {
        $totalStudents = User::query()
            ->where('role', 'student')
            ->where('is_active', true)
            ->count();

        $totalTeachers = User::query()
            ->where('role', 'teacher')
            ->where('is_active', true)
            ->count();

        $averageGrade = round((float) (Grade::query()->avg('value') ?? 0), 1);

        $absencesThisMonth = Absence::query()
            ->whereMonth('absence_date', now()->month)
            ->whereYear('absence_date', now()->year)
            ->count();

        return response()->json([
            'total_students' => (int) $totalStudents,
            'total_teachers' => (int) $totalTeachers,
            'average_grade' => $averageGrade,
            'absences_this_month' => (int) $absencesThisMonth,
        ]);
    }

    private function schoolYearRange(): array
    {
        $now = Carbon::now();
        $schoolYearStartYear = $now->month >= 9 ? $now->year : $now->year - 1;

        $startDate = Carbon::create($schoolYearStartYear, 9, 1)->startOfDay();
        $endDate = $now->copy()->endOfMonth();

        return [$startDate, $endDate];
    }

    private function monthKeyExpression(): string
    {
        return match (config('database.default')) {
            'sqlite' => "strftime('%Y-%m', absence_date)",
            'pgsql' => "to_char(absence_date, 'YYYY-MM')",
            'sqlsrv' => "FORMAT(absence_date, 'yyyy-MM')",
            default => "DATE_FORMAT(absence_date, '%Y-%m')",
        };
    }

    private function resolveParentStudentId(User $parent, int $requestedStudentId): int
    {
        $childIds = $parent->parentChildren()
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($childIds->isEmpty()) {
            return 0;
        }

        if ($requestedStudentId > 0) {
            return $childIds->contains($requestedStudentId) ? $requestedStudentId : 0;
        }

        return (int) $childIds->first();
    }
}
