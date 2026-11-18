<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_mahasiswa_login_redirects_to_student_dashboard(): void
    {
        $user = User::factory()->role(Role::Mahasiswa)->create(['username' => 'mhs']);

        $response = $this->post('/login', [
            'username' => 'mhs',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('mahasiswa.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_lecturer_and_admin_redirect_to_their_dashboards(): void
    {
        $lecturer = User::factory()->role(Role::GuruDosen)->create(['username' => 'dosen']);
        $this->post('/login', ['username' => 'dosen', 'password' => 'password'])
            ->assertRedirect(route('lecturer.dashboard'));

        $this->post('/logout');

        $admin = User::factory()->role(Role::Admin)->create(['username' => 'admin']);
        $this->post('/login', ['username' => 'admin', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertNotNull($lecturer);
    }

    public function test_invalid_credentials_are_rejected_with_generic_message(): void
    {
        User::factory()->create(['username' => 'mhs']);

        $this->post('/login', ['username' => 'mhs', 'password' => 'salah-password'])
            ->assertSessionHasErrors(['username' => 'Username atau password tidak sesuai.']);

        $this->assertGuest();
    }

    public function test_inactive_account_cannot_login(): void
    {
        User::factory()->inactive()->create(['username' => 'nonaktif']);

        $this->post('/login', ['username' => 'nonaktif', 'password' => 'password'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        User::factory()->create(['username' => 'mhs']);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/login', ['username' => 'mhs', 'password' => 'salah']);
        }

        $response = $this->post('/login', ['username' => 'mhs', 'password' => 'password']);

        $response->assertSessionHasErrors('username');
        $this->assertStringContainsString('Terlalu banyak percobaan login', session('errors')->first('username'));
        $this->assertGuest();
    }

    public function test_temporary_password_user_is_redirected_to_password_change(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
            'temporary_password_expires_at' => now()->addHours(72),
        ]);

        $this->post('/login', ['username' => $user->username, 'password' => 'password'])
            ->assertRedirect(route('account.password.edit'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_with_unrotated_password_cannot_open_other_pages(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
            'temporary_password_expires_at' => now()->addHours(72),
        ]);

        $this->actingAs($user)
            ->get('/account')
            ->assertRedirect(route('account.password.edit'));

        $this->actingAs($user)
            ->get('/mahasiswa/dashboard')
            ->assertRedirect(route('account.password.edit'));
    }

    public function test_expired_temporary_password_cannot_login(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
            'temporary_password_expires_at' => now()->subMinute(),
        ]);

        $this->post('/login', ['username' => $user->username, 'password' => 'password'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_expired_temporary_password_ends_existing_session(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
            'temporary_password_expires_at' => now()->subMinute(),
        ]);

        $this->actingAs($user)
            ->get('/account/password')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_authenticated_user_is_redirected_away_from_login_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect(route('mahasiswa.dashboard'));
    }
}
