<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PBI12PengujianTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function createBendahara(): User
    {
        return User::factory()->create([
            'role' => 'bendahara',
            'status' => 'active',
        ]);
    }

    public function test_bendahara_can_view_user_list(): void
    {
        $bendahara = $this->createBendahara();

        User::factory()->create([
            'name' => 'Pelanggan Satu',
            'role' => 'masyarakat',
            'status' => 'active',
        ]);

        $response = $this->actingAs($bendahara)
            ->get(route('bendahara.pengguna.index'));

        $response->assertOk();
        $response->assertSee('Pelanggan Satu');
    }

    public function test_bendahara_can_search_user_by_name(): void
    {
        $bendahara = $this->createBendahara();

        User::factory()->create([
            'name' => 'Pelanggan Dicari',
            'role' => 'masyarakat',
        ]);

        User::factory()->create([
            'name' => 'Pelanggan Lain',
            'role' => 'masyarakat',
        ]);

        $response = $this->actingAs($bendahara)
            ->get(route('bendahara.pengguna.index', [
                'search' => 'Pelanggan Dicari',
            ]));

        $response->assertOk();
        $response->assertSee('Pelanggan Dicari');
        $response->assertDontSee('Pelanggan Lain');
    }

    public function test_bendahara_can_filter_users_by_role(): void
    {
        $bendahara = $this->createBendahara();

        User::factory()->create([
            'name' => 'Warga Filter',
            'role' => 'masyarakat',
        ]);

        User::factory()->create([
            'name' => 'Petugas Filter',
            'role' => 'petugas',
        ]);

        $response = $this->actingAs($bendahara)
            ->get(route('bendahara.pengguna.index', [
                'role' => 'masyarakat',
            ]));

        $response->assertOk();
        $response->assertSee('Warga Filter');
        $response->assertDontSee('Petugas Filter');
    }

    public function test_bendahara_can_open_customer_edit_page(): void
    {
        $bendahara = $this->createBendahara();

        $customer = User::factory()->create([
            'name' => 'Pelanggan Edit',
            'role' => 'masyarakat',
        ]);

        $response = $this->actingAs($bendahara)
            ->get(route('bendahara.pengguna.edit', $customer));

        $response->assertOk();
        $response->assertSee('Pelanggan Edit');
    }

    public function test_non_bendahara_cannot_access_user_management(): void
    {
        $petugas = User::factory()->create([
            'role' => 'petugas',
            'status' => 'active',
        ]);

        $response = $this->actingAs($petugas)
            ->get(route('bendahara.pengguna.index'));

        $response->assertForbidden();
    }

    public function test_bendahara_can_update_officer_profile(): void
    {
        $bendahara = $this->createBendahara();

        $officer = User::factory()->create([
            'role' => 'petugas',
            'status' => 'active',
        ]);

        $response = $this->actingAs($bendahara)
            ->put(route('bendahara.pengguna.update', $officer), [
                'name' => 'Petugas Baru',
                'email' => 'petugasbaru@satuair.test',
                'phone' => '08123456789',
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $officer->id,
            'name' => 'Petugas Baru',
            'email' => 'petugasbaru@satuair.test',
        ]);
    }

    public function test_officer_update_requires_valid_name_and_email(): void
    {
        $bendahara = $this->createBendahara();

        $officer = User::factory()->create([
            'role' => 'petugas',
        ]);

        $response = $this->actingAs($bendahara)
            ->put(route('bendahara.pengguna.update', $officer), [
                'name' => '',
                'email' => 'email-tidak-valid',
                'phone' => '08123456789',
            ]);

        $response->assertSessionHasErrors([
            'name',
            'email',
        ]);
    }
}
