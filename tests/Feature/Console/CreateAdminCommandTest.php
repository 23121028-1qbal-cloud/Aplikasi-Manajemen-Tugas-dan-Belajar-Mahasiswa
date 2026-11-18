<?php

namespace Tests\Feature\Console;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_hashed_admin_account(): void
    {
        $this->artisan('app:create-admin', [
            'username' => 'admin-utama',
            '--password' => 'rahasia-admin',
        ])->assertExitCode(0);

        $admin = User::where('username', 'admin-utama')->firstOrFail();

        $this->assertSame(Role::Admin, $admin->role);
        $this->assertTrue($admin->is_active);
        $this->assertTrue(Hash::check('rahasia-admin', $admin->password));
    }

    public function test_command_rejects_duplicate_username(): void
    {
        User::factory()->create(['username' => 'admin-utama']);

        $this->artisan('app:create-admin', [
            'username' => 'admin-utama',
            '--password' => 'rahasia-admin',
        ])->assertExitCode(1);

        $this->assertSame(1, User::where('username', 'admin-utama')->count());
    }

    public function test_command_rejects_short_password(): void
    {
        $this->artisan('app:create-admin', [
            'username' => 'admin-baru',
            '--password' => 'pendek',
        ])->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['username' => 'admin-baru']);
    }
}
