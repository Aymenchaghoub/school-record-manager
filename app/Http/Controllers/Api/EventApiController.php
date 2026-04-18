<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class EventApiController extends Controller
{
    use ApiResponse;

    private const TYPES = [
        'exam',
        'meeting',
        'holiday',
        'sports',
        'cultural',
        'parent_meeting',
        'other',
    ];

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = min(max((int) $request->input('per_page', 15), 1), 500);

        $query = Event::query()
            ->with([
                'class:id,name,code,level,section',
                'createdBy:id,name,email',
            ])
            ->latest('start_date');

        $this->applyRoleScope($query, $request->user());

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        return $this->paginated($query->paginate($perPage)->withQueryString(), 'Events fetched successfully.');
    }

    public function parentIndex(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $this->canMutate($request->user())) {
            return $this->error('You are not allowed to create events.', [], 403);
        }

        $validated = $this->validatePayload($request);
        $payload = $this->normalizePayload($validated, $request->user(), false);

        $event = Event::create($payload);

        return $this->success(
            $event->load(['class:id,name,code', 'createdBy:id,name,email']),
            'Event created successfully.',
            201
        );
    }

    public function show(Request $request, Event $event): JsonResponse
    {
        $ownedEvent = $this->resolveOwnedEvent($event->id, $request->user());

        if (! $ownedEvent) {
            return $this->error('Event not found.', [], 404);
        }

        return $this->success($ownedEvent, 'Event fetched successfully.');
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        if (! $this->canMutate($request->user())) {
            return $this->error('You are not allowed to update events.', [], 403);
        }

        $ownedEvent = $this->resolveOwnedEvent($event->id, $request->user());

        if (! $ownedEvent) {
            return $this->error('Event not found.', [], 404);
        }

        $validated = $this->validatePayload($request, true);
        $payload = $this->normalizePayload($validated, $request->user(), true, $ownedEvent);

        $ownedEvent->update($payload);

        return $this->success(
            $ownedEvent->fresh()->load(['class:id,name,code', 'createdBy:id,name,email']),
            'Event updated successfully.'
        );
    }

    public function destroy(Request $request, Event $event): JsonResponse
    {
        if (! $this->canMutate($request->user())) {
            return $this->error('You are not allowed to delete events.', [], 403);
        }

        $ownedEvent = $this->resolveOwnedEvent($event->id, $request->user());

        if (! $ownedEvent) {
            return $this->error('Event not found.', [], 404);
        }

        $ownedEvent->delete();

        return $this->success(null, 'Event deleted successfully.');
    }

    private function applyRoleScope(Builder $query, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isTeacher()) {
            $classIds = $user->teacherClasses()->pluck('classes.id')->unique();

            $query->where(function ($builder) use ($user, $classIds) {
                $builder->where('created_by', $user->id)
                    ->orWhereNull('class_id')
                    ->orWhereIn('class_id', $classIds);
            });

            return;
        }

        if ($user->isStudent()) {
            $classId = $user->studentClass()?->id;

            $query->where('is_published', true)
                ->where(function ($builder) use ($classId) {
                    $builder->whereNull('class_id');

                    if ($classId) {
                        $builder->orWhere('class_id', $classId);
                    }
                });

            return;
        }

        if ($user->isParent()) {
            $classIds = $user->parentChildren()
                ->get()
                ->map(fn (User $child) => $child->studentClass()?->id)
                ->filter()
                ->unique()
                ->values();

            $query->where('is_published', true)
                ->where(function ($builder) use ($classIds) {
                    $builder->whereNull('class_id')
                        ->orWhereIn('class_id', $classIds);
                });

            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function resolveOwnedEvent(int $eventId, User $user): ?Event
    {
        $query = Event::query()
            ->with([
                'class:id,name,code,level,section',
                'createdBy:id,name,email',
            ])
            ->whereKey($eventId);

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
            'title' => [$required, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => [$required, Rule::in(self::TYPES)],
            'start_date' => [$required, 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'location' => ['nullable', 'string', 'max:255'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'is_public' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
            'color' => ['nullable', 'string', 'max:20'],
            'target_audience' => ['nullable', 'array'],
            'target_audience.*' => ['string', 'max:50'],
        ]);
    }

    private function normalizePayload(array $validated, User $user, bool $isUpdate, ?Event $existingEvent = null): array
    {
        if (! empty($validated['start_date'])) {
            $startDate = Carbon::parse($validated['start_date']);
        } elseif ($isUpdate && $existingEvent?->start_date) {
            $startDate = Carbon::parse($existingEvent->start_date);
        } else {
            $startDate = now();
        }

        $validated['start_date'] = $startDate;

        if (! empty($validated['end_date'])) {
            $validated['end_date'] = Carbon::parse($validated['end_date']);
        } elseif (! $isUpdate && ! isset($validated['end_date'])) {
            $validated['end_date'] = null;
        }

        if (! isset($validated['target_audience']) && ! $isUpdate) {
            $validated['target_audience'] = [];
        }

        if (! isset($validated['is_public']) && ! $isUpdate) {
            $validated['is_public'] = true;
        }

        if (! isset($validated['is_published']) && ! $isUpdate) {
            $validated['is_published'] = true;
        }

        if (! $isUpdate) {
            $validated['created_by'] = $user->id;
        }

        return $validated;
    }
}
