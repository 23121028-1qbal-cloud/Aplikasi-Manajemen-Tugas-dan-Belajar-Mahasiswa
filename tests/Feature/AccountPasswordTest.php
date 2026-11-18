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
            ->assertRedirect(route('account.password.edit'));

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
