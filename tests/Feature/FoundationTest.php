<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_guest_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_login_page_renders_guest_layout(): void
    {
        $this->withoutVite();

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('data-theme="light"', false);
        $response->assertSee('Lewati ke konten utama');
        $response->assertSee('Masuk');
    }

    public function test_validation_summary_links_to_invalid_fields(): void
    {
        $this->withoutVite();

        $response = $this->followingRedirects()->from('/register')->post('/register', [
            'npm' => '',
            'nama_lengkap' => '',
            'username' => '',
            'password' => 'pendek',
            'password_confirmation' => 'pendek',
        ]);

        $response->assertOk();
        $response->assertSee('id="validation-errors"', false);
        $response->assertSee('href="#npm"', false);
        $response->assertSee('role="alert"', false);
    }

    public function test_theme_attribute_follows_cookie_preference(): void
    {
        $this->withoutVite();

        $response = $this->withUnencryptedCookie('theme', 'dark')->get('/login');

        $response->assertOk();
        $response->assertSee('data-theme="dark"', false);
    }

    public function test_authenticated_layout_renders_navigation_shell(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->view('layouts.app', ['title' => 'Dashboard']);

        $response->assertSee('id="appNav"', false);
        $response->assertSee('id="main-content"', false);
        $response->assertSee('Navigasi utama');
        $response->assertSee('app-sidebar', false);
    }
}
