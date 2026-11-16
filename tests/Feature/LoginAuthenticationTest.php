<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_available_to_guests(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Masuk ke Akun');
    }

    public function test_active_user_can_login_and_is_redirected_by_role(): void
    {
        $user = User::factory()->create([
            'role' => 'petugas',
            'status' => 'active',
            'password' => 'secret-password',
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'secret-password',
            'role' => 'petugas',
        ])->assertRedirect('/petugas/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'secret-password']);

        $this->from(route('login'))->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
            'role' => 'masyarakat',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_rejects_a_role_that_does_not_match_the_account(): void
    {
        $user = User::factory()->create([
            'role' => 'petugas',
            'password' => 'secret-password',
        ]);

        $this->from(route('login'))->post(route('login'), [
            'email' => $user->email,
            'password' => 'secret-password',
            'role' => 'masyarakat',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('role');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'role' => 'masyarakat',
            'status' => 'inactive',
            'password' => 'secret-password',
        ]);

        $this->from(route('login'))->post(route('login'), [
            'email' => $user->email,
            'password' => 'secret-password',
            'role' => 'masyarakat',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_ends_the_authenticated_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
