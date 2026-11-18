<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_for_protected_routes(): void
    {
        $this->get('/mahasiswa/dashboard')->assertRedirect(route('login'));
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
        $this->get('/account')->assertRedirect(route('login'));
    }

    public function test_mahasiswa_cannot_access_other_role_dashboards(): void
    {
        $student = User::factory()->role(Role::Mahasiswa)->create();

        $this->actingAs($student)->get('/guru-dosen/dashboard')->assertForbidden();
        $this->actingAs($student)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($student)->get('/admin/lecturers')->assertForbidden();
    }

    public function test_lecturer_cannot_access_admin_area(): void
    {
        $lecturer = User::factory()->role(Role::GuruDosen)->create();

        $this->actingAs($lecturer)->get('/guru-dosen/dashboard')->assertOk();
        $this->actingAs($lecturer)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($lecturer)->post('/admin/lecturers', [])->assertForbidden();
    }

    public function test_admin_cannot_access_student_area(): void
    {
        $admin = User::factory()->role(Role::Admin)->create();

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->actingAs($admin)->get('/mahasiswa/dashboard')->assertForbidden();
    }

    public function test_deactivated_user_session_is_terminated(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->actingAs($user)
            ->get('/account')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
