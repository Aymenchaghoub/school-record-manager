<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Classes\StoreClassRequest;
use App\Http\Requests\Api\Classes\UpdateClassRequest;
use App\Models\ClassModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);

        $query = ClassModel::query()
            ->with(['responsibleTeacher:id,name,email'])
            ->withCount('students')
            ->latest();

        if ($request->user()->isTeacher()) {
            $teacherClassIds = $request->user()->teacherClasses()->pluck('classes.id')->unique();
            $teacherId = $request->user()->id;

            $query->where(function ($builder) use ($teacherClassIds, $teacherId) {
                $builder->whereIn('id', $teacherClassIds)
                    ->orWhere('responsible_teacher_id', $teacherId);
            });
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('level', 'like', "%{$search}%")
                    ->orWhere('section', 'like', "%{$search}%")
                    ->orWhere('academic_year', 'like', "%{$search}%");
            });
        }

        return $this->paginated($query->paginate($perPage)->withQueryString(), 'Classes fetched successfully.');
    }

    public function store(StoreClassRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $payload = $this->normalizePayload($validated);

        $class = ClassModel::create($payload);

        return $this->success(
            $class->load(['responsibleTeacher:id,name,email']),
            'Class created successfully.',
            201
        );
    }

    public function show(ClassModel $class): JsonResponse
    {
        return $this->success(
            $class->load(['responsibleTeacher:id,name,email'])->loadCount('students'),
            'Class fetched successfully.'
        );
    }

    public function update(UpdateClassRequest $request, ClassModel $class): JsonResponse
    {
        $validated = $request->validated();
        $payload = $this->normalizePayload($validated);

        $class->update($payload);

        return $this->success(
            $class->fresh()->load(['responsibleTeacher:id,name,email'])->loadCount('students'),
            'Class updated successfully.'
        );
    }

    public function destroy(ClassModel $class): JsonResponse
    {
        $class->delete();

        return $this->success(null, 'Class deleted successfully.');
    }

    private function normalizePayload(array $validated): array
    {
        if (isset($validated['teacher_id']) && ! isset($validated['responsible_teacher_id'])) {
            $validated['responsible_teacher_id'] = $validated['teacher_id'];
        }

        unset($validated['teacher_id']);

        return $validated;
    }
}
