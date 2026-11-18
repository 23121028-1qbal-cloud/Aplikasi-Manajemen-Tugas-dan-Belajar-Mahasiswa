<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'npm' => '20260001',
            'nama_lengkap' => 'Budi Santoso',
            'username' => 'budi',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ], $overrides);
    }

    public function test_student_can_register_and_is_logged_in(): void
    {
        $response = $this->post('/register', $this->payload());

        $response->assertRedirect(route('mahasiswa.dashboard'));

        $user = User::where('username', 'budi')->firstOrFail();

        $this->assertSame(Role::Mahasiswa, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('mahasiswa', [
            'id_user' => $user->id_user,
            'npm' => '20260001',
            'nama_lengkap' => 'Budi Santoso',
        ]);
    }

    public function test_registration_ignores_submitted_role(): void
    {
        $this->post('/register', $this->payload(['role' => 'admin']));

        $user = User::where('username', 'budi')->firstOrFail();

        $this->assertSame(Role::Mahasiswa, $user->role);
    }

    public function test_duplicate_username_is_rejected(): void
    {
        User::factory()->create(['username' => 'budi']);

        $this->post('/register', $this->payload())
            ->assertSessionHasErrors('username');

        $this->assertSame(1, User::where('username', 'budi')->count());
    }

    public function test_duplicate_npm_is_rejected(): void
    {
        $existing = Mahasiswa::factory()->create(['npm' => '20260001']);

        $this->post('/register', $this->payload())
            ->assertSessionHasErrors('npm');

        $this->assertSame(1, Mahasiswa::where('npm', '20260001')->count());
        $this->assertNotNull($existing->user);
    }

    public function test_password_must_be_confirmed_and_meet_minimum_length(): void
    {
        $this->post('/register', $this->payload(['password_confirmation' => 'berbeda123']))
            ->assertSessionHasErrors('password');

        $this->post('/register', $this->payload(['npm' => '20260002', 'username' => 'budi2', 'password' => 'pendek', 'password_confirmation' => 'pendek']))
            ->assertSessionHasErrors('password');

        $this->assertGuest();
    }
}
