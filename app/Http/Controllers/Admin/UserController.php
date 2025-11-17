<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by role
        if ($request->has('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Include soft deleted if requested
        if ($request->boolean('include_deleted')) {
            $query->withTrashed();
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistics - always show total counts regardless of filters
        $statistics = [
            'total_users' => User::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_teachers' => User::where('role', 'teacher')->count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_parents' => User::where('role', 'parent')->count(),
        ];

        return view('admin.users.index', compact('users', 'statistics'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $classes = ClassModel::where('is_active', true)->get();
        $parents = User::where('role', 'parent')->where('is_active', true)->get();
        
        return view('admin.users.create', compact('classes', 'parents'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', 'in:admin,teacher,student,parent'],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'address' => ['nullable', 'string'],
            'class_id' => ['nullable', 'exists:classes,id', 'required_if:role,student'],
            'parent_ids' => ['nullable', 'array'],
            'parent_ids.*' => ['exists:users,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => true,
        ]);

        // If student, enroll in class
        if ($user->role === 'student' && isset($validated['class_id'])) {
            $user->studentClasses()->attach($validated['class_id'], [
                'enrollment_date' => now(),
                'status' => 'active',
            ]);

            // Link parents if provided
            if (isset($validated['parent_ids'])) {
                foreach ($validated['parent_ids'] as $parentId) {
                    $user->studentParents()->attach($parentId, [
                        'relationship' => 'guardian',
                        'is_primary_contact' => false,
                    ]);
                }
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load([
            'studentClass',
            'studentGrades',
            'studentAbsences',
            'studentReportCards',
            'studentParents',
            'parentChildren',
            'teacherClasses',
            'teacherSubjects',
        ]);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $classes = ClassModel::where('is_active', true)->get();
        $parents = User::where('role', 'parent')->where('is_active', true)->get();
        
        return view('admin.users.edit', compact('user', 'classes', 'parents'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'address' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Update class enrollment for students
        if ($user->role === 'student' && isset($validated['class_id'])) {
            // Deactivate current enrollment
            $user->studentClasses()->updateExistingPivot(
                $user->studentClass()?->id,
                ['status' => 'transferred']
            );

            // Create new enrollment
            $user->studentClasses()->attach($validated['class_id'], [
                'enrollment_date' => now(),
                'status' => 'active',
            ]);
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Soft delete the specified user
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Restore a soft deleted user
     */
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('admin.users.index')
            ->with('success', 'User restored successfully.');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "User {$status} successfully.");
    }

    /**
     * Bulk delete users
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        // Remove current user from the list
        $userIds = array_diff($validated['user_ids'], [auth()->id()]);

        User::whereIn('id', $userIds)->delete();

        return back()->with('success', count($userIds) . ' users deleted successfully.');
    }

    /**
     * Display grades overview for admin
     */
    public function gradesIndex()
    {
        $grades = \App\Models\Grade::with(['student', 'subject', 'class', 'teacher'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.grades.index', compact('grades'));
    }

    /**
     * Display absences overview for admin
     */
    public function absencesIndex()
    {
        $absences = \App\Models\Absence::with(['student', 'class'])
            ->orderBy('absence_date', 'desc')
            ->paginate(20);

        return view('admin.absences.index', compact('absences'));
    }

    /**
     * Display reports overview for admin
     */
    public function reportsIndex()
    {
        $reportCards = \App\Models\ReportCard::with(['student', 'class'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $statistics = [
            'total_users' => User::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_teachers' => User::where('role', 'teacher')->count(),
            'total_classes' => \App\Models\ClassModel::count(),
            'total_subjects' => \App\Models\Subject::count(),
        ];

        return view('admin.reports.index', compact('reportCards', 'statistics'));
    }
}
