<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /**
     * Test admin can view users list
     */
    public function test_admin_can_view_users_list()
    {
        User::factory()->count(5)->create();
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
        $response->assertViewHas('users');
    }

    /**
     * Test non-admin cannot access users list
     */
    public function test_non_admin_cannot_access_users_list()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        
        $response = $this->actingAs($teacher)
            ->get(route('admin.users.index'));
        
        $response->assertStatus(403);
    }

    /**
     * Test admin can create a new student
     */
    public function test_admin_can_create_student()
    {
        $class = ClassModel::factory()->create();
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'New Student',
                'email' => 'newstudent@school.com',
                'password' => 'password123',
                'role' => 'student',
                'phone' => '1234567890',
                'date_of_birth' => '2010-01-01',
                'gender' => 'male',
                'address' => '123 School St',
                'class_id' => $class->id,
            ]);
        
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('users', [
            'email' => 'newstudent@school.com',
            'role' => 'student',
        ]);
        
        // Check student is enrolled in class
        $student = User::where('email', 'newstudent@school.com')->first();
        $this->assertDatabaseHas('student_classes', [
            'student_id' => $student->id,
            'class_id' => $class->id,
            'status' => 'active',
        ]);
    }

    /**
     * Test admin can create a teacher
     */
    public function test_admin_can_create_teacher()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'New Teacher',
                'email' => 'newteacher@school.com',
                'password' => 'password123',
                'role' => 'teacher',
                'phone' => '0987654321',
            ]);
        
        $response->assertRedirect(route('admin.users.index'));
        
        $this->assertDatabaseHas('users', [
            'email' => 'newteacher@school.com',
            'role' => 'teacher',
        ]);
    }

    /**
     * Test admin can update user
     */
    public function test_admin_can_update_user()
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'role' => 'student',
        ]);
        
        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'Updated Name',
                'email' => $user->email,
                'phone' => '9999999999',
                'is_active' => true,
            ]);
        
        $response->assertRedirect(route('admin.users.show', $user));
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'phone' => '9999999999',
        ]);
    }

    /**
     * Test admin can soft delete user
     */
    public function test_admin_can_soft_delete_user()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $user));
        
        $response->assertRedirect(route('admin.users.index'));
        
        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    /**
     * Test admin cannot delete themselves
     */
    public function test_admin_cannot_delete_themselves()
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->admin));
        
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * Test admin can restore soft deleted user
     */
    public function test_admin_can_restore_deleted_user()
    {
        $user = User::factory()->create();
        $user->delete();
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.restore', $user->id));
        
        $response->assertRedirect(route('admin.users.index'));
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * Test admin can toggle user status
     */
    public function test_admin_can_toggle_user_status()
    {
        $user = User::factory()->create(['is_active' => true]);
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.toggle-status', $user));
        
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => false,
        ]);
    }

    /**
     * Test user search functionality
     */
    public function test_user_search_functionality()
    {
        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['search' => 'John']));
        
        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertDontSee('Jane Smith');
    }

    /**
     * Test user filter by role
     */
    public function test_user_filter_by_role()
    {
        User::factory()->create(['role' => 'teacher']);
        User::factory()->create(['role' => 'student']);
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['role' => 'teacher']));
        
        $response->assertStatus(200);
        $users = $response->viewData('users');
        
        foreach ($users as $user) {
            $this->assertEquals('teacher', $user->role);
        }
    }

    /**
     * Test bulk delete users
     */
    public function test_bulk_delete_users()
    {
        $users = User::factory()->count(3)->create();
        $userIds = $users->pluck('id')->toArray();
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.bulk-delete'), [
                'user_ids' => $userIds,
            ]);
        
        $response->assertSessionHas('success');
        
        foreach ($userIds as $id) {
            $this->assertSoftDeleted('users', ['id' => $id]);
        }
    }

    /**
     * Test email uniqueness validation
     */
    public function test_email_uniqueness_validation()
    {
        User::factory()->create(['email' => 'existing@school.com']);
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'New User',
                'email' => 'existing@school.com',
                'password' => 'password123',
                'role' => 'student',
            ]);
        
        $response->assertSessionHasErrors('email');
    }
}
