<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Sumber Rezeki');
        $response->assertDontSee('RAM');
        $response->assertDontSee('Kelapa Sawit');
        $response->assertSee('Admin@2026');
    }

    public function test_admin_can_login_and_redirect_to_admin_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'password' => bcrypt('Admin@2026'),
        ]);

        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'Admin@2026',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard'));

        $dashboardResponse = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Dashboard Operasional');
    }

    public function test_owner_can_login_and_redirect_to_owner_dashboard(): void
    {
        $owner = User::create([
            'name' => 'Owner User',
            'username' => 'owner',
            'email' => 'owner@test.com',
            'role' => 'owner',
            'password' => bcrypt('Owner@2026'),
        ]);

        $response = $this->post('/login', [
            'username' => 'owner',
            'password' => 'Owner@2026',
        ]);

        $this->assertAuthenticatedAs($owner);
        $response->assertRedirect(route('owner.dashboard'));

        $dashboardResponse = $this->actingAs($owner)->get(route('owner.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Dashboard Owner');
    }

    public function test_admin_cannot_access_owner_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($admin)->get(route('owner.dashboard'));
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_owner_cannot_access_admin_dashboard(): void
    {
        $owner = User::create([
            'name' => 'Owner User',
            'username' => 'owner',
            'role' => 'owner',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($owner)->get(route('admin.dashboard'));
        $response->assertRedirect(route('owner.dashboard'));
    }
}
