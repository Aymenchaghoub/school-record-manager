<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\User;
use App\Services\GradeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportCardApiController extends Controller
{
    use ApiResponse;

    private const CONDUCT_GRADES = [
        'Excellent',
        'Very Good',
        'Good',
        'Fair',
        'Poor',
    ];

    public function __construct(private readonly GradeService $gradeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = min(max((int) $request->input('per_page', 15), 1), 500);

        $query = ReportCard::query()
            ->with([
                'student:id,name,email',
                'class:id,name,code,level,section',
            ])
            ->latest('issue_date');

        $this->applyRoleScope($query, $request->user());

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('term', 'like', "%{$search}%")
                    ->orWhere('academic_year', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('class', function ($classQuery) use ($search) {
                        $classQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

            $paginator = $query->paginate($perPage)->withQueryString();
            $paginator->setCollection(
                $paginator->getCollection()->map(fn (ReportCard $reportCard) => $this->transformReportCard($reportCard))
            );

            return $this->paginated($paginator, 'Report cards fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return $this->error('You are not allowed to create report cards.', [], 403);
        }

        $validated = $this->validatePayload($request);
            $payload = $this->preparePayload($validated);

            $reportCard = ReportCard::create($payload)->load(['student:id,name,email', 'class:id,name,code']);

        return $this->success(
                $this->transformReportCard($reportCard),
            'Report card created successfully.',
            201
        );
    }

    public function show(Request $request, ReportCard $reportCard): JsonResponse
    {
        $ownedReportCard = $this->resolveOwnedReportCard($reportCard->id, $request->user());

        if (! $ownedReportCard) {
            return $this->error('Report card not found.', [], 404);
        }

        return $this->success($this->transformReportCard($ownedReportCard), 'Report card fetched successfully.');
    }

    public function update(Request $request, ReportCard $reportCard): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return $this->error('You are not allowed to update report cards.', [], 403);
        }

        $validated = $this->validatePayload($request, true);
        $payload = $this->preparePayload($validated, $reportCard);

        $reportCard->update($payload);

        $freshReportCard = $reportCard->fresh()->load(['student:id,name,email', 'class:id,name,code']);

        return $this->success(
            $this->transformReportCard($freshReportCard),
            'Report card updated successfully.'
        );
    }

    public function destroy(Request $request, ReportCard $reportCard): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return $this->error('You are not allowed to delete report cards.', [], 403);
        }

        $reportCard->delete();

        return $this->success(null, 'Report card deleted successfully.');
    }

    private function applyRoleScope(Builder $query, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isStudent()) {
            $query->where('student_id', $user->id);
            return;
        }

        if ($user->isParent()) {
            $childIds = $user->parentChildren()->pluck('users.id');

            if ($childIds->isEmpty()) {
                $query->whereRaw('1 = 0');
                return;
            }

            $query->whereIn('student_id', $childIds);
            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function resolveOwnedReportCard(int $reportCardId, User $user): ?ReportCard
    {
        $query = ReportCard::query()
            ->with([
                'student:id,name,email',
                'class:id,name,code,level,section',
            ])
            ->whereKey($reportCardId);

        $this->applyRoleScope($query, $user);

        return $query->first();
    }

    private function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';

        $validated = $request->validate([
            'student_id' => [$required, 'integer', Rule::exists('users', 'id')->where('role', 'student')],
            'class_id' => [$required, 'integer', 'exists:classes,id'],
            'term' => [$required, 'max:100'],
            'academic_year' => ['sometimes', 'string', 'max:30'],
            'year' => ['sometimes', 'integer', 'digits:4'],
            'overall_average' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'total_absences' => ['nullable', 'integer', 'min:0'],
            'justified_absences' => ['nullable', 'integer', 'min:0'],
            'rank_in_class' => ['nullable', 'integer', 'min:1'],
            'total_students' => ['nullable', 'integer', 'min:1'],
            'subject_grades' => ['nullable', 'array'],
            'principal_remarks' => ['nullable', 'string'],
            'teacher_remarks' => ['nullable', 'string'],
            'conduct_grade' => ['nullable', Rule::in(self::CONDUCT_GRADES)],
            'issue_date' => ['nullable', 'date'],
            'is_final' => ['sometimes', 'boolean'],
        ]);

        if (! isset($validated['academic_year']) && isset($validated['year'])) {
            $validated['academic_year'] = (string) $validated['year'];
        }

        if (! isset($validated['academic_year']) && ! $isUpdate) {
            $validated['academic_year'] = (string) now()->year;
        }

        unset($validated['year']);

        return $validated;
    }

    private function preparePayload(array $validated, ?ReportCard $existing = null): array
    {
        $studentId = (int) ($validated['student_id'] ?? $existing?->student_id ?? 0);
        $classId = (int) ($validated['class_id'] ?? $existing?->class_id ?? 0);

        $activeTerm = isset($validated['term'])
            ? (string) $validated['term']
            : (string) ($existing?->term ?? '');

        $subjects = $this->buildSubjectSummaries(
            $studentId,
            $classId,
            $activeTerm,
            $validated['subject_grades'] ?? ($existing?->subject_grades ?? [])
        );

        $fallbackAverage = array_key_exists('overall_average', $validated)
            ? (float) $validated['overall_average']
            : ($existing?->overall_average !== null ? (float) $existing->overall_average : null);

        $validated['student_id'] = $studentId;
        $validated['class_id'] = $classId;
        $validated['term'] = $activeTerm;
        $validated['academic_year'] = (string) ($validated['academic_year'] ?? $existing?->academic_year ?? now()->year);
        $validated['issue_date'] = $validated['issue_date'] ?? ($existing?->issue_date?->toDateString() ?? now()->toDateString());
        $validated['is_final'] = array_key_exists('is_final', $validated)
            ? (bool) $validated['is_final']
            : ($existing?->is_final ?? false);
        $validated['total_absences'] = (int) ($validated['total_absences'] ?? $existing?->total_absences ?? 0);
        $validated['justified_absences'] = (int) ($validated['justified_absences'] ?? $existing?->justified_absences ?? 0);
        $validated['rank_in_class'] = isset($validated['rank_in_class'])
            ? (int) $validated['rank_in_class']
            : ($existing?->rank_in_class ?? null);
        $validated['total_students'] = isset($validated['total_students'])
            ? (int) $validated['total_students']
            : ($existing?->total_students ?? null);
        $validated['subject_grades'] = $subjects;
        $validated['overall_average'] = $this->computeOverallAverage($subjects, $fallbackAverage);

        return $validated;
    }

    private function transformReportCard(ReportCard $reportCard): array
    {
        $reportCard->loadMissing([
            'student:id,name,email',
            'class:id,name,code,level,section',
        ]);

        $subjects = $this->buildSubjectSummaries(
            (int) $reportCard->student_id,
            (int) $reportCard->class_id,
            (string) $reportCard->term,
            $reportCard->subject_grades ?? []
        );

        $data = $reportCard->toArray();
        $data['overall_average'] = $this->computeOverallAverage(
            $subjects,
            isset($data['overall_average']) ? (float) $data['overall_average'] : null
        );
        $data['subjects'] = $subjects;
        $data['subject_grades'] = $subjects;

        return $data;
    }

    private function buildSubjectSummaries(int $studentId, int $classId, ?string $term = null, array $fallbackGrades = []): array
    {
        $class = ClassModel::query()
            ->with('subjects:id,name')
            ->find($classId);

        if ($class && $class->subjects->isNotEmpty()) {
            return $class->subjects
                ->map(function ($subject) use ($studentId, $classId, $term) {
                    $gradesQuery = Grade::query()
                        ->where('student_id', $studentId)
                        ->where('class_id', $classId)
                        ->where('subject_id', $subject->id)
                        ->when($term !== null && trim($term) !== '', function ($query) use ($term) {
                            $query->where('term', $term);
                        });

                    $grades = $gradesQuery
                        ->get(['value', 'weight']);

                    $average = null;

                    if ($grades->isNotEmpty()) {
                        $weightedScore = 0.0;
                        $totalWeight = 0.0;

                        foreach ($grades as $grade) {
                            $weight = (float) ($grade->weight ?: 1);
                            $weightedScore += ((float) $grade->value) * $weight;
                            $totalWeight += $weight;
                        }

                        $average = $totalWeight > 0
                            ? round($weightedScore / $totalWeight, 2)
                            : null;
                    }

                    return [
                        'subject' => $subject->name,
                        'average' => $average !== null ? round((float) $average, 2) : null,
                        'max' => 20,
                        'label' => $average !== null ? $this->gradeService->performanceLabel((float) $average) : null,
                    ];
                })
                ->values()
                ->all();
        }

        return $this->mapLegacySubjectGrades($fallbackGrades);
    }

    private function mapLegacySubjectGrades(array $legacyGrades): array
    {
        return collect($legacyGrades)
            ->map(function ($grade) {
                $subjectName = $grade['subject'] ?? $grade['subject_name'] ?? 'Matiere';
                $average = isset($grade['average']) && is_numeric($grade['average'])
                    ? round((float) $grade['average'], 2)
                    : null;

                return [
                    'subject' => $subjectName,
                    'average' => $average,
                    'max' => 20,
                    'label' => $average !== null ? $this->gradeService->performanceLabel((float) $average) : null,
                ];
            })
            ->values()
            ->all();
    }

    private function computeOverallAverage(array $subjects, ?float $fallbackAverage = null): ?float
    {
        $averages = collect($subjects)
            ->pluck('average')
            ->filter(fn ($value) => $value !== null)
            ->map(fn ($value) => (float) $value);

        if ($averages->isNotEmpty()) {
            return round((float) $averages->avg(), 2);
        }

        return $fallbackAverage !== null ? round((float) $fallbackAverage, 2) : null;
    }
}
