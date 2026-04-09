<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Absences\StoreAbsenceRequest;
use App\Http\Requests\Api\Absences\UpdateAbsenceRequest;
use App\Models\Absence;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbsenceApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $month = (int) $request->input('month', 0);
        $year = (int) $request->input('year', 0);

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

        if ($month >= 1 && $month <= 12) {
            $query->whereMonth('absence_date', $month);
        }

        if ($year >= 1900) {
            $query->whereYear('absence_date', $year);
        }

        return $this->paginated($query->paginate(10)->withQueryString(), 'Absences fetched successfully.');
    }

    public function store(StoreAbsenceRequest $request): JsonResponse
    {
        if (! $this->canMutate($request->user())) {
            return $this->error('You are not allowed to create absences.', [], 403);
        }

        $validated = $request->validated();
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

    public function update(UpdateAbsenceRequest $request, Absence $absence): JsonResponse
    {
        if (! $this->canMutate($request->user())) {
            return $this->error('You are not allowed to update absences.', [], 403);
        }

        $ownedAbsence = $this->resolveOwnedAbsence($absence->id, $request->user());

        if (! $ownedAbsence) {
            return $this->error('Absence not found.', [], 404);
        }

        $validated = $request->validated();
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
