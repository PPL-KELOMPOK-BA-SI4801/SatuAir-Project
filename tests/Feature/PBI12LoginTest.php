<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PBI12LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
            'role' => 'masyarakat',
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
            'role' => 'masyarakat',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'role' => 'masyarakat',
            'status' => 'active',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
            'role' => 'masyarakat',
        ]);

        $this->assertGuest();
    }

    public function test_user_cannot_login_with_wrong_role(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
            'role' => 'masyarakat',
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
            'role' => 'bendahara',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('role');
    }
        public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
            'role' => 'masyarakat',
            'status' => 'inactive',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
            'role' => 'masyarakat',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'role' => 'masyarakat',
            'status' => 'active',
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_login_requires_email_password_and_role(): void
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors([
            'email',
            'password',
            'role',
        ]);

        $this->assertGuest();
    }
}