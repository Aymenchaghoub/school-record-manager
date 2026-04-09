<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GradeApiController extends Controller
{
    use ApiResponse;

    private const TYPES = [
        'exam',
        'quiz',
        'assignment',
        'project',
        'participation',
        'midterm',
        'final',
    ];

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);

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

        return $this->paginated($query->paginate($perPage)->withQueryString(), 'Grades fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        if (! $this->canMutate($request->user())) {
            return $this->error('You are not allowed to create grades.', [], 403);
        }

        $validated = $this->validatePayload($request);
        $payload = $this->normalizePayload($validated, $request->user(), false);

        $grade = Grade::create($payload);

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

    public function update(Request $request, Grade $grade): JsonResponse
    {
        if (! $this->canMutate($request->user())) {
            return $this->error('You are not allowed to update grades.', [], 403);
        }

        $ownedGrade = $this->resolveOwnedGrade($grade->id, $request->user());

        if (! $ownedGrade) {
            return $this->error('Grade not found.', [], 404);
        }

        $validated = $this->validatePayload($request, true);
        $payload = $this->normalizePayload($validated, $request->user(), true);

        $ownedGrade->update($payload);

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
            $query->where('teacher_id', $user->id);
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

    private function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';

        return $request->validate([
            'student_id' => [$required, 'integer', 'exists:users,id'],
            'subject_id' => [$required, 'integer', 'exists:subjects,id'],
            'class_id' => [$required, 'integer', 'exists:classes,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'value' => [$required, 'numeric', 'min:0'],
            'max_value' => ['nullable', 'numeric', 'gt:0'],
            'type' => [$required, Rule::in(self::TYPES)],
            'title' => ['nullable', 'string', 'max:255'],
            'grade_date' => [$required, 'date'],
            'term' => ['nullable', 'string', 'max:100'],
            'weight' => ['nullable', 'numeric', 'gt:0'],
            'comment' => ['nullable', 'string'],
        ]);
    }

    private function normalizePayload(array $validated, User $user, bool $isUpdate): array
    {
        if ($user->isTeacher()) {
            $validated['teacher_id'] = $user->id;
        } elseif (! $isUpdate && empty($validated['teacher_id'])) {
            $validated['teacher_id'] = $user->id;
        }

        if (! $isUpdate && ! isset($validated['max_value'])) {
            $validated['max_value'] = 100;
        }

        if (! $isUpdate && ! isset($validated['weight'])) {
            $validated['weight'] = 1;
        }

        return $validated;
    }
}
