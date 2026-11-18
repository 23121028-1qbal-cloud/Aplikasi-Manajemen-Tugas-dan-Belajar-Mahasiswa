<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_change_password_with_current_password(): void
    {
        $user = User::factory()->create(['password' => 'password-lama']);

        $this->actingAs($user)
            ->put('/account/password', [
                'current_password' => 'password-lama',
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
            ])
            ->assertRedirect(route('mahasiswa.dashboard'));

        $this->assertTrue(Hash::check('password-baru', $user->fresh()->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => 'password-lama']);

        $this->actingAs($user)
            ->put('/account/password', [
                'current_password' => 'salah',
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password-lama', $user->fresh()->password));
    }

    public function test_changing_password_clears_temporary_password_flags(): void
    {
        $user = User::factory()->create([
            'password' => 'sementara123',
            'must_change_password' => true,
            'temporary_password_expires_at' => now()->addDay(),
        ]);

        $this->actingAs($user)->put('/account/password', [
            'current_password' => 'sementara123',
            'password' => 'permanen123',
            'password_confirmation' => 'permanen123',
        ])->assertRedirect(route('mahasiswa.dashboard'));

        $user->refresh();

        $this->assertFalse($user->must_change_password);
        $this->assertNull($user->temporary_password_expires_at);
    }

    public function test_new_password_must_be_at_least_eight_characters(): void
    {
        $user = User::factory()->create(['password' => 'password-lama']);

        $this->actingAs($user)
            ->put('/account/password', [
                'current_password' => 'password-lama',
                'password' => 'pendek',
                'password_confirmation' => 'pendek',
            ])
            ->assertSessionHasErrors('password');
    }
}
