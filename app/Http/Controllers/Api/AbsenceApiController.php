<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AbsenceApiController extends Controller
{
    use ApiResponse;

    private const TYPES = [
        'full_day',
        'partial',
        'late_arrival',
        'early_departure',
    ];

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);

        $query = Absence::query()
            ->with([
                'student:id,name,email',
                'class:id,name,code,level,section',
                'subject:id,name,code',
                'recordedBy:id,name,email',
            ])
            ->latest('absence_date');

        $this->applyRoleScope($query, $request->user());

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('reason', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('class', function ($classQuery) use ($search) {
                        $classQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $this->paginated($query->paginate($perPage)->withQueryString(), 'Absences fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        if (! $this->canMutate($request->user())) {
            return $this->error('You are not allowed to create absences.', [], 403);
        }

        $validated = $this->validatePayload($request);
        $payload = $this->normalizePayload($validated, $request->user(), false);

        $absence = Absence::create($payload);

        return $this->success(
            $absence->load(['student:id,name,email', 'class:id,name,code', 'subject:id,name,code', 'recordedBy:id,name,email']),
            'Absence created successfully.',
            201
        );
    }

    public function show(Request $request, Absence $absence): JsonResponse
    {
        $absence = $this->resolveOwnedAbsence($absence->id, $request->user());

        if (! $absence) {
            return $this->error('Absence not found.', [], 404);
        }

        return $this->success($absence, 'Absence fetched successfully.');
    }

    public function update(Request $request, Absence $absence): JsonResponse
    {
        if (! $this->canMutate($request->user())) {
            return $this->error('You are not allowed to update absences.', [], 403);
        }

        $ownedAbsence = $this->resolveOwnedAbsence($absence->id, $request->user());

        if (! $ownedAbsence) {
            return $this->error('Absence not found.', [], 404);
        }

        $validated = $this->validatePayload($request, true);
        $payload = $this->normalizePayload($validated, $request->user(), true);

        $ownedAbsence->update($payload);

        return $this->success(
            $ownedAbsence->fresh()->load(['student:id,name,email', 'class:id,name,code', 'subject:id,name,code', 'recordedBy:id,name,email']),
            'Absence updated successfully.'
        );
    }

    public function destroy(Request $request, Absence $absence): JsonResponse
    {
        if (! $this->canMutate($request->user())) {
            return $this->error('You are not allowed to delete absences.', [], 403);
        }

        $ownedAbsence = $this->resolveOwnedAbsence($absence->id, $request->user());

        if (! $ownedAbsence) {
            return $this->error('Absence not found.', [], 404);
        }

        $ownedAbsence->delete();

        return $this->success(null, 'Absence deleted successfully.');
    }

    private function applyRoleScope(Builder $query, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isTeacher()) {
            $query->where('recorded_by', $user->id);
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

    private function resolveOwnedAbsence(int $absenceId, User $user): ?Absence
    {
        $query = Absence::query()
            ->with([
                'student:id,name,email',
                'class:id,name,code,level,section',
                'subject:id,name,code',
                'recordedBy:id,name,email',
            ])
            ->whereKey($absenceId);

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
            'class_id' => [$required, 'integer', 'exists:classes,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'recorded_by' => ['nullable', 'integer', 'exists:users,id'],
            'absence_date' => [$required, 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'is_justified' => ['sometimes', 'boolean'],
            'type' => [$required, Rule::in(self::TYPES)],
            'reason' => ['nullable', 'string', 'max:255'],
            'justification' => ['nullable', 'string'],
            'justification_document' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function normalizePayload(array $validated, User $user, bool $isUpdate): array
    {
        if ($user->isTeacher()) {
            $validated['recorded_by'] = $user->id;
        } elseif (! $isUpdate && empty($validated['recorded_by'])) {
            $validated['recorded_by'] = $user->id;
        }

        if (! $isUpdate && ! isset($validated['is_justified'])) {
            $validated['is_justified'] = false;
        }

        return $validated;
    }
}
