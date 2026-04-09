<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ReportCard;
use App\Models\User;
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

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);

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

        return $this->paginated($query->paginate($perPage)->withQueryString(), 'Report cards fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return $this->error('You are not allowed to create report cards.', [], 403);
        }

        $validated = $this->validatePayload($request);
        $reportCard = ReportCard::create($validated);

        return $this->success(
            $reportCard->load(['student:id,name,email', 'class:id,name,code']),
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

        return $this->success($ownedReportCard, 'Report card fetched successfully.');
    }

    public function update(Request $request, ReportCard $reportCard): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return $this->error('You are not allowed to update report cards.', [], 403);
        }

        $validated = $this->validatePayload($request, true);
        $reportCard->update($validated);

        return $this->success(
            $reportCard->fresh()->load(['student:id,name,email', 'class:id,name,code']),
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

        return $request->validate([
            'student_id' => [$required, 'integer', 'exists:users,id'],
            'class_id' => [$required, 'integer', 'exists:classes,id'],
            'term' => [$required, 'string', 'max:100'],
            'academic_year' => [$required, 'string', 'max:30'],
            'overall_average' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'total_absences' => ['nullable', 'integer', 'min:0'],
            'justified_absences' => ['nullable', 'integer', 'min:0'],
            'rank_in_class' => ['nullable', 'integer', 'min:1'],
            'total_students' => ['nullable', 'integer', 'min:1'],
            'subject_grades' => [$required, 'array'],
            'principal_remarks' => ['nullable', 'string'],
            'teacher_remarks' => ['nullable', 'string'],
            'conduct_grade' => ['nullable', Rule::in(self::CONDUCT_GRADES)],
            'issue_date' => [$required, 'date'],
            'is_final' => ['sometimes', 'boolean'],
        ]);
    }
}
