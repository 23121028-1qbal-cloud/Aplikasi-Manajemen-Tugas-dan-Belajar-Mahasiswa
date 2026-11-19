<?php

namespace Tests\Feature\Account;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppearanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_open_appearance_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('account.appearance.edit'))
            ->assertOk()
            ->assertSee('Light Mode')
            ->assertSee('Dark Mode');
    }

    public function test_user_can_set_dark_theme_cookie(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->patch(route('account.appearance.update'), ['theme' => 'dark']);

        $response->assertRedirect(route('account.appearance.edit'));
        $response->assertSessionHas('status');
        $response->assertPlainCookie('theme', 'dark');

        $this->actingAs($user)
            ->withUnencryptedCookie('theme', 'dark')
            ->get(route('account.appearance.edit'))
            ->assertOk()
            ->assertSee('data-theme="dark"', false);
    }

    public function test_invalid_theme_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('account.appearance.update'), ['theme' => 'sepia'])
            ->assertSessionHasErrors('theme');
    }

    public function test_guest_cannot_access_appearance_settings(): void
    {
        $this->get(route('account.appearance.edit'))->assertRedirect(route('login'));
    }
}
