<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_without_indexing(): void
    {
        config(['site.indexable' => true]);

        $this->get('/jhasseaidsha12')
            ->assertOk()
            ->assertSee('name="_token"', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_the_admin_area_and_the_old_login_path_are_hidden_from_guests(): void
    {
        $this->get('/admin')->assertNotFound();
        $this->get('/admin/tools')->assertNotFound();
        $this->get('/admin/login')->assertNotFound();
    }

    public function test_admin_can_log_in(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/jhasseaidsha12', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/jhasseaidsha12', ['email' => $admin->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors(['email' => 'These credentials do not match an admin account.']);

        $this->assertGuest();
    }

    public function test_non_admin_cannot_log_in(): void
    {
        $user = User::factory()->create();

        $this->post('/jhasseaidsha12', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_non_admin_session_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_login_is_locked_after_repeated_failures(): void
    {
        $admin = User::factory()->admin()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/jhasseaidsha12', ['email' => $admin->email, 'password' => 'wrong-password']);
        }

        $this->post('/jhasseaidsha12', ['email' => $admin->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertStringStartsWith('Too many sign-in attempts.', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_admin_can_log_out(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post('/admin/logout')
            ->assertRedirect('/jhasseaidsha12');

        $this->assertGuest();
    }

    public function test_create_admin_command_creates_an_admin(): void
    {
        $this->artisan('admin:create', ['--name' => 'Site Owner', '--email' => 'owner@example.com', '--password' => 'a-long-secure-password'])
            ->assertSuccessful();

        $this->assertTrue(User::where('email', 'owner@example.com')->value('is_admin'));
    }

    public function test_create_admin_command_rejects_short_password(): void
    {
        $this->artisan('admin:create', ['--name' => 'Site Owner', '--email' => 'owner@example.com', '--password' => 'short'])
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'owner@example.com']);
    }
}
