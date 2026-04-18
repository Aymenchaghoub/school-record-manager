<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = min(max((int) $request->input('per_page', 15), 1), 500);

        $query = Subject::query()
            ->with(['teacher:id,name,email'])
            ->latest();

        if ($request->user()->isTeacher()) {
            $teacherId = $request->user()->id;
            $query->where(function ($builder) use ($teacherId) {
                $builder->where('teacher_id', $teacherId)
                    ->orWhereHas('teachers', function ($teacherQuery) use ($teacherId) {
                        $teacherQuery->where('users.id', $teacherId);
                    });
            });
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        return $this->paginated($query->paginate($perPage)->withQueryString(), 'Subjects fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request);
        $subject = Subject::create($validated);

        return $this->success(
            $subject->load(['teacher:id,name,email']),
            'Subject created successfully.',
            201
        );
    }

    public function show(Subject $subject): JsonResponse
    {
        return $this->success(
            $subject->load(['teacher:id,name,email']),
            'Subject fetched successfully.'
        );
    }

    public function update(Request $request, Subject $subject): JsonResponse
    {
        $validated = $this->validatePayload($request, $subject);

        $subject->update($validated);

        return $this->success(
            $subject->fresh()->load(['teacher:id,name,email']),
            'Subject updated successfully.'
        );
    }

    public function destroy(Subject $subject): JsonResponse
    {
        $subject->delete();

        return $this->success(null, 'Subject deleted successfully.');
    }

    private function validatePayload(Request $request, ?Subject $subject = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('subjects', 'code')->ignore($subject?->id),
            ],
            'description' => ['nullable', 'string'],
            'credits' => ['nullable', 'integer', 'min:1', 'max:10'],
            'type' => ['required', Rule::in(['core', 'elective', 'extracurricular'])],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
