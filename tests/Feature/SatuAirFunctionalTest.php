<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\WaterBill;
use App\Models\WaterMeter;
use App\Models\Payment;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\Area;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SatuAirFunctionalTest extends TestCase
{
    /**
     * Test 1: Halaman Landing Page Publik dapat diakses.
     */
    public function test_landing_page_can_be_rendered(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('SatuAir');
    }

    /**
     * Test 2: Registrasi akun baru masyarakat (Role: Masyarakat, Master Data Pelanggan).
     */
    public function test_masyarakat_can_register(): void
    {
        User::where('email', 'wargabaru@gmail.com')->delete();

        $response = $this->post('/register', [
            'name' => 'Warga Cibodas Baru',
            'email' => 'wargabaru@gmail.com',
            'phone' => '089988776655',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/masyarakat/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => 'wargabaru@gmail.com',
            'role' => 'masyarakat',
        ]);
    }

    /**
     * Test 3: Login Multi-Role (Masyarakat, Petugas, Bendahara).
     */
    public function test_user_can_login_with_correct_role(): void
    {
        $user = User::where('email', 'rina@gmail.com')->first();

        $response = $this->post('/login', [
            'email' => 'rina@gmail.com',
            'password' => 'password123',
            'role' => 'masyarakat',
        ]);

        $response->assertRedirect('/masyarakat/dashboard');
        $this->assertAuthenticatedAs($user);

        // Logout user pertama sebelum test login berikutnya
        $this->post('/logout');

        // Test login Bendahara
        $bendahara = User::whereIn('role', ['bendahara', 'admin'])->first();
        if ($bendahara) {
            $respBendahara = $this->post('/login', [
                'email' => $bendahara->email,
                'password' => 'password123',
                'role' => 'bendahara',
            ]);
            $respBendahara->assertRedirect('/bendahara/dashboard');
            $this->assertAuthenticatedAs($bendahara);
        }
    }

    /**
     * Test 4: Dashboard Masyarakat menampilkan tagihan dan ringkasan.
     */
    public function test_masyarakat_dashboard_displays_bills(): void
    {
        $user = User::where('email', 'rina@gmail.com')->first();

        $response = $this->actingAs($user)->get('/masyarakat/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Rina Marlina');
        $response->assertSee('Tagihan Menunggu Pembayaran');

        // Pastikan akun Budi Santoso juga memiliki tagihan menunggu pembayaran bulan September
        $budi = User::where('email', 'budi@gmail.com')->first();
        $respBudi = $this->actingAs($budi)->get('/masyarakat/dashboard');
        $respBudi->assertStatus(200);
        $respBudi->assertSee('Budi Santoso');
        $respBudi->assertSee('Tagihan Menunggu Pembayaran');
        $respBudi->assertSee('Periode 2026-09');
    }

    /**
     * Test 5: Transaksi Pembayaran Online (Antar Peran & Kanal Digital: QRIS / Bank / E-Wallet).
     */
    public function test_masyarakat_can_submit_online_payment_transaction(): void
    {
        $user = User::where('email', 'rina@gmail.com')->first();
        $bill = WaterBill::where('user_id', $user->id)->where('status', 'unpaid')->first();

        $this->assertNotNull($bill, 'Harus ada tagihan unpaid untuk ditest');

        $response = $this->actingAs($user)->post('/masyarakat/payments', [
            'water_bill_id' => $bill->id,
            'payment_method' => 'qris',
        ]);

        $payment = Payment::where('water_bill_id', $bill->id)->latest()->first();
        $response->assertRedirect(route('masyarakat.payments.instruction', $payment->id));
        $this->assertDatabaseHas('payments', [
            'water_bill_id' => $bill->id,
            'user_id' => $user->id,
            'payment_method' => 'qris',
            'status' => 'pending',
        ]);
    }

    /**
     * Test 6: Pembuatan Pengaduan Gangguan Air oleh Masyarakat.
     */
    public function test_masyarakat_can_report_water_complaint(): void
    {
        $user = User::where('email', 'rina@gmail.com')->first();
        $category = ComplaintCategory::first();

        $response = $this->actingAs($user)->post('/masyarakat/complaints', [
            'category_id' => $category->id,
            'location' => 'RT 01 RW 01 Dusun Krajan',
            'description' => 'Pipa depan rumah bocor dan air keluar deras.',
        ]);

        $response->assertRedirect(route('masyarakat.complaints.index'));
        $this->assertDatabaseHas('complaints', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'location' => 'RT 01 RW 01 Dusun Krajan',
            'status' => 'pending',
        ]);
    }

    /**
     * Test 7: Dashboard Bendahara & Mini Reporting (Klasemen Musim / Pendapatan Antar-Tahun).
     */
    public function test_admin_dashboard_and_reporting_can_be_accessed(): void
    {
        $bendahara = User::whereIn('role', ['bendahara', 'admin'])->first();

        $response = $this->actingAs($bendahara)->get('/bendahara/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Sistem Monitoring Terpadu SatuAir');
        $response->assertSee('Tabel Rekapitulasi Kas Masuk Per Bulan');
    }
}
