<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Tests\TestCase;

class ApiVersioningTest extends TestCase
{
    public function test_v1_login_endpoint_is_available(): void
    {
        $this->postJson('/api/v1/login', [])->assertStatus(422);
    }

    public function test_legacy_non_versioned_admin_endpoint_is_not_available(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->getJson('/api/admin/users')
            ->assertStatus(404);
    }

    public function test_v1_admin_endpoint_is_accessible_for_admin_role(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/users')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
