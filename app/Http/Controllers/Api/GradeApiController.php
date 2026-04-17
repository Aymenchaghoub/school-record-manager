<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Grades\StoreGradeRequest;
use App\Http\Requests\Api\Grades\UpdateGradeRequest;
use App\Models\Grade;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\GradeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeApiController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly GradeService $gradeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $subject = trim((string) $request->input('subject', ''));
        $perPage = min(max((int) $request->input('per_page', 15), 1), 500);

        $query = Grade::query()
            ->with([
                'student:id,name,email',
                'subject:id,name,code',
                'class:id,name,code,level,section',
                'teacher:id,name,email',
            ])
            ->latest();

        $this->applyRoleScope($query, $request->user());

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('term', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('subject', function ($subjectQuery) use ($search) {
                        $subjectQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($subject !== '') {
            $query->whereHas('subject', function ($subjectQuery) use ($subject) {
                $subjectQuery->where('name', $subject);
            });
        }

        $subjectOptionsQuery = Grade::query()
            ->join('subjects', 'grades.subject_id', '=', 'subjects.id')
            ->select('subjects.name')
            ->distinct()
            ->orderBy('subjects.name');

        $this->applyRoleScope($subjectOptionsQuery, $request->user());

        $subjects = $subjectOptionsQuery
            ->pluck('subjects.name')
            ->filter()
            ->values();

        return $this->paginated(
            $query->paginate($perPage)->withQueryString(),
            'Grades fetched successfully.',
            ['subjects' => $subjects]
        );
    }

    public function store(StoreGradeRequest $request): JsonResponse
    {
        if (! $this->canMutate($request->user())) {
            return $this->error('You are not allowed to create grades.', [], 403);
        }

        $validated = $request->validated();
        $payload = $this->normalizePayload($validated, $request->user(), false);

        $grade = $this->gradeService->create($payload);

        ActivityLogger::grade('created', (int) $request->user()->id, [
            'grade_id' => $grade->id,
            'student_id' => $grade->student_id,
            'subject_id' => $grade->subject_id,
        ]);

        return $this->success(
            $grade->load(['student:id,name,email', 'subject:id,name,code', 'class:id,name,code', 'teacher:id,name,email']),
            'Grade created successfully.',
            201
        );
    }

    public function show(Request $request, Grade $grade): JsonResponse
    {
        $grade = $this->resolveOwnedGrade($grade->id, $request->user());

        if (! $grade) {
            return $this->error('Grade not found.', [], 404);
        }

        return $this->success($grade, 'Grade fetched successfully.');
    }

    public function update(UpdateGradeRequest $request, Grade $grade): JsonResponse
    {
        if (! $this->canMutate($request->user())) {
            return $this->error('You are not allowed to update grades.', [], 403);
        }

        $ownedGrade = $this->resolveOwnedGrade($grade->id, $request->user());

        if (! $ownedGrade) {
            return $this->error('Grade not found.', [], 404);
        }

        $validated = $request->validated();
        $payload = $this->normalizePayload($validated, $request->user(), true);

        $ownedGrade = $this->gradeService->update($ownedGrade, $payload);

        return $this->success(
            $ownedGrade->fresh()->load(['student:id,name,email', 'subject:id,name,code', 'class:id,name,code', 'teacher:id,name,email']),
            'Grade updated successfully.'
        );
    }

    public function destroy(Request $request, Grade $grade): JsonResponse
    {
        if (! $this->canMutate($request->user())) {
            return $this->error('You are not allowed to delete grades.', [], 403);
        }

        $ownedGrade = $this->resolveOwnedGrade($grade->id, $request->user());

        if (! $ownedGrade) {
            return $this->error('Grade not found.', [], 404);
        }

        $ownedGrade->delete();

        return $this->success(null, 'Grade deleted successfully.');
    }

    private function applyRoleScope(Builder $query, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isTeacher()) {
            $query->where('grades.teacher_id', $user->id);
            return;
        }

        if ($user->isStudent()) {
            $query->where('grades.student_id', $user->id);
            return;
        }

        if ($user->isParent()) {
            $childIds = $user->parentChildren()->pluck('users.id');

            if ($childIds->isEmpty()) {
                $query->whereRaw('1 = 0');
                return;
            }

            $query->whereIn('grades.student_id', $childIds);
            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function resolveOwnedGrade(int $gradeId, User $user): ?Grade
    {
        $query = Grade::query()
            ->with([
                'student:id,name,email',
                'subject:id,name,code',
                'class:id,name,code,level,section',
                'teacher:id,name,email',
            ])
            ->whereKey($gradeId);

        $this->applyRoleScope($query, $user);

        return $query->first();
    }

    private function canMutate(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    private function normalizePayload(array $validated, User $user, bool $isUpdate): array
    {
        if ($user->isTeacher()) {
            $validated['teacher_id'] = $user->id;
        } elseif (! $isUpdate && empty($validated['teacher_id'])) {
            $validated['teacher_id'] = $user->id;
        }

        return $validated;
    }
}
