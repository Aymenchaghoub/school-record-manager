<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Grade;
use App\Models\Absence;
use App\Models\ClassModel;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserFlowsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed basic data
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    /**
     * Test admin can create a new user
     */
    public function test_admin_can_create_new_user()
    {
        $admin = User::where('email', 'admin@school.com')->first();
        
        $userData = [
            'name' => 'New Test Teacher',
            'email' => 'new.teacher@school.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'teacher',
            'phone' => '+1234567890',
            'gender' => 'female'
        ];
        
        $response = $this->actingAs($admin)->post(route('admin.users.store'), $userData);
        
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('users', [
            'name' => 'New Test Teacher',
            'email' => 'new.teacher@school.com',
            'role' => 'teacher'
        ]);
    }

    /**
     * Test teacher can view their dashboard
     */
    public function test_teacher_can_view_dashboard()
    {
        $teacher = User::where('email', 'teacher@school.com')->first();
        
        $response = $this->actingAs($teacher)->get(route('teacher.dashboard'));
        
        $response->assertStatus(200);
        $response->assertViewIs('teacher.dashboard');
    }

    /**
     * Test teacher can view grades page
     */
    public function test_teacher_can_view_grades_page()
    {
        $teacher = User::where('email', 'teacher@school.com')->first();
        
        $response = $this->actingAs($teacher)->get(route('teacher.grades.index'));
        
        $response->assertStatus(200);
    }

    /**
     * Test student can view their dashboard
     */
    public function test_student_can_view_dashboard()
    {
        $student = User::where('email', 'student@school.com')->first();
        
        $response = $this->actingAs($student)->get(route('student.dashboard'));
        
        $response->assertStatus(200);
        $response->assertViewIs('student.dashboard');
    }

    /**
     * Test student can view their grades
     */
    public function test_student_can_view_their_grades()
    {
        $student = User::where('email', 'student@school.com')->first();
        
        $response = $this->actingAs($student)->get(route('student.grades.index'));
        
        $response->assertStatus(200);
    }

    /**
     * Test student can view their absences
     */
    public function test_student_can_view_their_absences()
    {
        $student = User::where('email', 'student@school.com')->first();
        
        $response = $this->actingAs($student)->get(route('student.absences'));
        
        $response->assertStatus(200);
    }

    /**
     * Test parent can view dashboard
     */
    public function test_parent_can_view_dashboard()
    {
        $parent = User::where('email', 'parent@school.com')->first();
        
        $response = $this->actingAs($parent)->get(route('parent.dashboard'));
        
        $response->assertStatus(200);
        $response->assertViewIs('parent.dashboard');
    }

    /**
     * Test parent can view children information
     */
    public function test_parent_can_view_children_information()
    {
        $parent = User::where('email', 'parent@school.com')->first();
        
        $response = $this->actingAs($parent)->get(route('parent.children.index'));
        
        $response->assertStatus(200);
    }

    /**
     * Test admin can view all users
     */
    public function test_admin_can_view_all_users()
    {
        $admin = User::where('email', 'admin@school.com')->first();
        
        $response = $this->actingAs($admin)->get(route('admin.users.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
    }

    /**
     * Test admin can view all classes
     */
    public function test_admin_can_view_all_classes()
    {
        $admin = User::where('email', 'admin@school.com')->first();
        
        $response = $this->actingAs($admin)->get(route('admin.classes.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.classes.index');
    }

    /**
     * Test admin can view all grades
     */
    public function test_admin_can_view_all_grades()
    {
        $admin = User::where('email', 'admin@school.com')->first();
        
        $response = $this->actingAs($admin)->get(route('admin.grades.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.grades.index');
    }

    /**
     * Test admin can view events
     */
    public function test_admin_can_view_events()
    {
        $admin = User::where('email', 'admin@school.com')->first();
        
        $response = $this->actingAs($admin)->get(route('admin.events.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.events.index');
    }

    /**
     * Test non-admin cannot access admin dashboard
     */
    public function test_non_admin_cannot_access_admin_dashboard()
    {
        $student = User::where('email', 'student@school.com')->first();
        
        $response = $this->actingAs($student)->get(route('admin.dashboard'));
        
        $response->assertStatus(403);
    }

    /**
     * Test student cannot access teacher pages
     */
    public function test_student_cannot_access_teacher_pages()
    {
        $student = User::where('email', 'student@school.com')->first();
        
        $response = $this->actingAs($student)->get(route('teacher.dashboard'));
        
        $response->assertStatus(403);
    }

    /**
     * Test password change works correctly
     */
    public function test_user_can_change_password()
    {
        $user = User::where('email', 'teacher@school.com')->first();
        $oldPassword = 'password';
        $newPassword = 'NewPassword123!';
        
        $response = $this->actingAs($user)->post(route('password.reset'), [
            'current_password' => $oldPassword,
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ]);
        
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        
        // Verify new password works
        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
    }
}
