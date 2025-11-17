<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ClassModel;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UIConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed basic data
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    /**
     * Test admin pages render correctly
     */
    public function test_admin_pages_render_correctly()
    {
        $admin = User::where('email', 'admin@school.com')->first();
        
        $response = $this->actingAs($admin);
        
        // Test dashboard
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        
        // Test users management
        $response = $this->get('/admin/users');
        $response->assertStatus(200);
        
        // Test classes management
        $response = $this->get('/admin/classes');
        $response->assertStatus(200);
        
        // Test subjects management
        $response = $this->get('/admin/subjects');
        $response->assertStatus(200);
        
        // Test grades overview
        $response = $this->get('/admin/grades');
        $response->assertStatus(200);
        
        // Test absences management
        $response = $this->get('/admin/absences');
        $response->assertStatus(200);
        
        // Test events management
        $response = $this->get('/admin/events');
        $response->assertStatus(200);
        
        // Test reports
        $response = $this->get('/admin/reports');
        $response->assertStatus(200);
    }
    
    /**
     * Test teacher pages render correctly
     */
    public function test_teacher_pages_render_correctly()
    {
        $teacher = User::where('email', 'teacher@school.com')->first();
        
        $response = $this->actingAs($teacher);
        
        // Test dashboard
        $response = $this->get('/teacher/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('teacher.dashboard');
        
        // Test grades
        $response = $this->get('/teacher/grades');
        $response->assertStatus(200);
        
        // Test absences
        $response = $this->get('/teacher/absences');
        $response->assertStatus(200);
        
        // Test classes
        $response = $this->get('/teacher/classes');
        $response->assertStatus(200);
    }
    
    /**
     * Test student pages render correctly
     */
    public function test_student_pages_render_correctly()
    {
        $student = User::where('email', 'student@school.com')->first();
        
        $response = $this->actingAs($student);
        
        // Test dashboard
        $response = $this->get('/student/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('student.dashboard');
        
        // Test grades
        $response = $this->get('/student/grades');
        $response->assertStatus(200);
        
        // Test absences
        $response = $this->get('/student/absences');
        $response->assertStatus(200);
        
        // Test report cards
        $response = $this->get('/student/report-cards');
        $response->assertStatus(200);
        
        // Test events
        $response = $this->get('/student/events');
        $response->assertStatus(200);
    }
    
    /**
     * Test parent pages render correctly
     */
    public function test_parent_pages_render_correctly()
    {
        $parent = User::where('email', 'parent@school.com')->first();
        
        $response = $this->actingAs($parent);
        
        // Test dashboard
        $response = $this->get('/parent/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('parent.dashboard');
        
        // Test children
        $response = $this->get('/parent/children');
        $response->assertStatus(200);
        
        // Test events
        $response = $this->get('/parent/events');
        $response->assertStatus(200);
    }
    
    /**
     * Test pages use consistent layout
     */
    public function test_pages_use_consistent_layout()
    {
        $admin = User::where('email', 'admin@school.com')->first();
        
        // Check admin pages extend app layout
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('layouts.app');
        
        $response = $this->actingAs($admin)->get('/admin/classes');
        $response->assertStatus(200);
        $response->assertSee('text-2xl font-bold text-gray-900'); // Check for consistent header styling
        
        $response = $this->actingAs($admin)->get('/admin/grades');
        $response->assertStatus(200);
        $response->assertSee('text-2xl font-bold text-gray-900');
    }
    
    /**
     * Test empty states use consistent components
     */
    public function test_empty_states_use_consistent_components()
    {
        $admin = User::where('email', 'admin@school.com')->first();
        
        // Clear all data first
        \DB::table('classes')->delete();
        \DB::table('grades')->delete();
        
        // Check empty state on classes page
        $response = $this->actingAs($admin)->get('/admin/classes');
        $response->assertStatus(200);
        $response->assertSee('No classes found');
        
        // Check empty state on grades page
        $response = $this->actingAs($admin)->get('/admin/grades');
        $response->assertStatus(200);
        $response->assertSee('No grades recorded');
    }
}
