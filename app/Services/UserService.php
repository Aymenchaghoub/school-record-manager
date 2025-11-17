<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Create a new user with role-specific handling
     */
    public function create(array $data): User
    {
        DB::beginTransaction();
        
        try {
            $data['password'] = Hash::make($data['password']);
            $data['is_active'] = $data['is_active'] ?? true;
            
            $user = User::create($data);
            
            // Handle role-specific relationships
            $this->handleRoleRelationships($user, $data);
            
            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Update user with role-specific handling
     */
    public function update(User $user, array $data): User
    {
        DB::beginTransaction();
        
        try {
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
            
            $user->update($data);
            
            // Handle role-specific relationships
            $this->handleRoleRelationships($user, $data);
            
            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Handle role-specific relationships
     */
    private function handleRoleRelationships(User $user, array $data): void
    {
        switch ($user->role) {
            case 'student':
                if (isset($data['class_id'])) {
                    $user->studentClasses()->sync([$data['class_id'] => [
                        'enrollment_date' => now(),
                        'status' => 'active'
                    ]]);
                }
                break;
                
            case 'parent':
                if (isset($data['children_ids'])) {
                    $syncData = [];
                    foreach ($data['children_ids'] as $childId) {
                        $syncData[$childId] = [
                            'relationship' => $data['relationship'] ?? 'parent',
                            'is_primary_contact' => true
                        ];
                    }
                    $user->parentChildren()->sync($syncData);
                }
                break;
                
            case 'teacher':
                if (isset($data['subject_ids'])) {
                    $user->teacherSubjects()->sync($data['subject_ids']);
                }
                break;
        }
    }
    
    /**
     * Get user statistics based on role
     */
    public function getUserStatistics(User $user): array
    {
        switch ($user->role) {
            case 'admin':
                return $this->getAdminStatistics();
            case 'teacher':
                return $this->getTeacherStatistics($user);
            case 'student':
                return $this->getStudentStatistics($user);
            case 'parent':
                return $this->getParentStatistics($user);
            default:
                return [];
        }
    }
    
    private function getAdminStatistics(): array
    {
        return [
            'total_users' => User::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_teachers' => User::where('role', 'teacher')->count(),
            'total_parents' => User::where('role', 'parent')->count(),
            'active_users' => User::where('is_active', true)->count(),
        ];
    }
    
    private function getTeacherStatistics(User $teacher): array
    {
        return [
            'total_classes' => $teacher->teacherClasses()->count(),
            'total_students' => $teacher->teacherClasses()
                ->withCount('students')
                ->get()
                ->sum('students_count'),
            'total_subjects' => $teacher->teacherSubjects()->count(),
        ];
    }
    
    private function getStudentStatistics(User $student): array
    {
        return [
            'current_class' => $student->studentClass()?->name,
            'total_subjects' => $student->studentClass()?->subjects()->count() ?? 0,
            'total_grades' => $student->grades()->count(),
            'total_absences' => $student->absences()->count(),
        ];
    }
    
    private function getParentStatistics(User $parent): array
    {
        return [
            'total_children' => $parent->parentChildren()->count(),
            'children_names' => $parent->parentChildren()->pluck('name')->toArray(),
        ];
    }
}
