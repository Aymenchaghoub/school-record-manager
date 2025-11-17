<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed basic data
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    /**
     * Test authenticated user can view profile page
     */
    public function test_authenticated_user_can_view_profile_page()
    {
        $user = User::where('email', 'admin@school.com')->first();
        
        $response = $this->actingAs($user)->get(route('profile.show'));
        
        $response->assertStatus(200);
        $response->assertViewIs('profile');
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    /**
     * Test unauthenticated user cannot view profile page
     */
    public function test_unauthenticated_user_cannot_view_profile_page()
    {
        $response = $this->get(route('profile.show'));
        
        $response->assertRedirect(route('login'));
    }

    /**
     * Test user can update profile information
     */
    public function test_user_can_update_profile_information()
    {
        $user = User::where('email', 'teacher@school.com')->first();
        
        $updatedData = [
            'name' => 'Updated Teacher Name',
            'email' => 'updated.teacher@school.com',
            'phone' => '+1234567890',
            'date_of_birth' => '1990-01-15',
            'gender' => 'male',
            'address' => '123 Updated Street, City'
        ];
        
        $response = $this->actingAs($user)->put(route('profile.update'), $updatedData);
        
        $response->assertRedirect(route('profile'));
        $response->assertSessionHas('success', 'Profile updated successfully.');
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Teacher Name',
            'email' => 'updated.teacher@school.com',
            'phone' => '+1234567890'
        ]);
    }

    /**
     * Test user cannot update profile with invalid email
     */
    public function test_user_cannot_update_profile_with_invalid_email()
    {
        $user = User::where('email', 'student@school.com')->first();
        $existingUser = User::where('email', 'admin@school.com')->first();
        
        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Test Student',
            'email' => 'admin@school.com', // Already exists
        ]);
        
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test user can upload profile photo
     * 
     * Note: Requires GD extension. Skipped if not available.
     */
    public function test_user_can_upload_profile_photo()
    {
        if (!function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension is not installed.');
        }
        
        Storage::fake('public');
        
        $user = User::where('email', 'parent@school.com')->first();
        
        $file = UploadedFile::fake()->image('avatar.jpg');
        
        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => $file
        ]);
        
        $response->assertRedirect(route('profile'));
        $response->assertSessionHas('success');
        
        // Verify file was stored
        $user->refresh();
        $this->assertNotNull($user->profile_photo);
        Storage::disk('public')->assertExists($user->profile_photo);
    }

    /**
     * Test authenticated user can logout
     */
    public function test_authenticated_user_can_logout()
    {
        $user = User::where('email', 'admin@school.com')->first();
        
        $response = $this->actingAs($user)->post(route('logout'));
        
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * Test unauthenticated user cannot logout
     */
    public function test_unauthenticated_user_cannot_logout()
    {
        $response = $this->post(route('logout'));
        
        $response->assertRedirect(route('login'));
    }
}
