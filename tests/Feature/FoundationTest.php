<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_guest_layout(): void
    {
        $this->withoutVite();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-theme="light"', false);
        $response->assertSee('Lewati ke konten utama');
        $response->assertSee('Fondasi aplikasi', false);
    }

    public function test_theme_attribute_follows_cookie_preference(): void
    {
        $this->withoutVite();

        $response = $this->withUnencryptedCookie('theme', 'dark')->get('/');

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
