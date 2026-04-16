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

        $query = ClassModel::query()
            ->with(['teacher:id,name,email'])
            ->withCount('students')
            ->latest();

        if ($request->user()->isTeacher()) {
            $teacherClassIds = $request->user()->teacherClasses()->pluck('classes.id')->unique();
            $teacherId = $request->user()->id;

            $query->where(function ($builder) use ($teacherClassIds, $teacherId) {
                $builder->whereIn('id', $teacherClassIds)
                    ->orWhere('teacher_id', $teacherId);
            });
        }

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        return $this->paginated($query->paginate(10)->withQueryString(), 'Classes fetched successfully.');
    }

    public function store(StoreClassRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $payload = $this->normalizePayload($validated);

        $class = ClassModel::create($payload);

        return $this->success(
            $class->load(['teacher:id,name,email']),
            'Class created successfully.',
            201
        );
    }

    public function show(ClassModel $class): JsonResponse
    {
        return $this->success(
            $class->load(['teacher:id,name,email'])->loadCount('students'),
            'Class fetched successfully.'
        );
    }

    public function update(UpdateClassRequest $request, ClassModel $class): JsonResponse
    {
        $validated = $request->validated();
        $payload = $this->normalizePayload($validated);

        $class->update($payload);

        return $this->success(
            $class->fresh()->load(['teacher:id,name,email'])->loadCount('students'),
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
        return $validated;
    }
}
