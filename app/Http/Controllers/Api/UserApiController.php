<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Users\StoreUserRequest;
use App\Http\Requests\Api\Users\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $role = trim((string) $request->input('role', ''));
        $isActive = $request->input('is_active');

        $query = User::query()->latest();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($role !== '') {
            $query->where('role', $role);
        }

        if ($isActive !== null && $isActive !== '') {
            $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true);
        }

        return $this->paginated($query->paginate(10)->withQueryString(), 'Users fetched successfully.');
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create($validated);

        return $this->success($user, 'User created successfully.', 201);
    }

    public function show(User $user): JsonResponse
    {
        return $this->success($user, 'User fetched successfully.');
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        if (array_key_exists('password', $validated) && empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return $this->success($user->fresh(), 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ((int) $request->user()->id === (int) $user->id) {
            return $this->error('You cannot delete your own account.', [], 422);
        }

        $user->delete();

        return $this->success(null, 'User deleted successfully.');
    }
}
