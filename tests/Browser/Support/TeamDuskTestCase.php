<?php

namespace Tests\Browser\Support;

use App\Models\{BillingPeriod, MeterReading, User, WaterMeter};
use Database\Seeders\AccessControlSeeder;
use Illuminate\Support\Facades\{Artisan, DB};
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

abstract class TeamDuskTestCase extends DuskTestCase
{
    protected function captureFailuresFor($browsers)
    {
        foreach ($browsers as $browser) {
            try { parent::captureFailuresFor(collect([$browser])); }
            catch (\Facebook\WebDriver\Exception\WebDriverException $exception) { fwrite(STDOUT, "\nScreenshot browser tidak tersedia: ".$exception->getMessage()."\n"); }
        }
    }

    protected function storeConsoleLogsFor($browsers)
    {
        foreach ($browsers as $browser) {
            try { parent::storeConsoleLogsFor(collect([$browser])); }
            catch (\Facebook\WebDriver\Exception\WebDriverException $exception) { fwrite(STDOUT, "\nLog browser tidak tersedia: ".$exception->getMessage()."\n"); }
        }
    }

    protected function closeAllButPrimary($browsers)
    {
        foreach ($browsers->slice(1) as $browser) {
            try { $browser->quit(); }
            catch (\Facebook\WebDriver\Exception\WebDriverException $exception) { /* Jangan tutupi error test asli saat cleanup. */ }
        }
        return $browsers->take(1);
    }

    public function browse(\Closure $callback)
    {
        try {
            return parent::browse($callback);
        } catch (\Throwable $exception) {
            fwrite(STDOUT, "\nDusk: ".$exception->getMessage()."\n");
            throw $exception;
        }
    }

    private static bool $schemaReady = false;
    protected User $bendahara;
    protected User $petugas;
    protected User $rina;
    protected User $budi;

    protected function setUp(): void
    {
        parent::setUp();
        // Guard sebelum reset: jangan gunakan DatabaseMigrations pada database kerja.
        DuskEnvironment::guard();
        $headers = get_headers(rtrim(config('app.url'), '/').'/login', true,
            stream_context_create(['http' => ['timeout' => 10]]));
        if (($headers['X-SatuAir-Dusk-Database'] ?? null) !== 'satuair_dusk_jira') {
            throw new \RuntimeException('Server browser bukan server Dusk terpisah. Database belum direset.');
        }
        Browser::$waitSeconds = 10;
        DuskEnvironment::clearFault();
        if (! self::$schemaReady) {
            Artisan::call('migrate', ['--force' => true]);
            self::$schemaReady = true;
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach (DB::select('SHOW TABLES') as $row) {
                $table = (array_values((array) $row))[0];
                if ($table !== 'migrations') DB::table($table)->truncate();
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
        $this->seed(AccessControlSeeder::class);
        $this->bendahara = $this->account('Pak Hendra', 'bendahara', 'admin@gmail.com');
        $this->petugas = $this->account('Ahmad Fauzi', 'petugas', 'petugas@gmail.com');
        $this->rina = $this->account('Rina Marlina', 'masyarakat', 'rina@gmail.com', 'CUST-00001');
        $this->budi = $this->account('Budi Santoso', 'masyarakat', 'budi@gmail.com', 'CUST-00002');
        BillingPeriod::create(['year' => now()->year, 'month' => now()->month,
            'billing_period' => now()->format('Y-m'), 'status' => 'active',
            'opened_by' => $this->bendahara->id, 'opened_at' => now()]);
    }

    protected function tearDown(): void
    {
        DuskEnvironment::clearFault();
        parent::tearDown();
    }

    protected function newBrowser($driver)
    {
        return new DemoBrowser($driver);
    }

    protected function account(string $name, string $role, string $email, ?string $customer = null): User
    {
        return User::create(['name' => $name, 'role' => $role, 'email' => $email,
            'password' => 'password123', 'status' => 'active', 'customer_id' => $customer,
            'phone' => '081234567890', 'address' => 'Dusun I, Cibodas']);
    }

    protected function login(Browser $browser, User $user): Browser
    {
        $browser->visit('/login')->deleteAllCookies()->visit('/login')->waitUntil('window.Alpine !== undefined');
        $browser->press(match ($user->role) {'petugas' => 'Petugas', 'bendahara' => 'Bendahara', default => 'Warga'})
            ->waitUntil('document.getElementById("role_field").value === '.json_encode($user->role))
            ->type('email', $user->email)->type('password', 'password123')->press('Masuk ke Akun')
            ->waitForLocation('/'.$user->role.'/dashboard');
        return $browser;
    }

    protected function meter(bool $history = true, ?User $owner = null): WaterMeter
    {
        $meter = WaterMeter::create(['user_id' => ($owner ?? $this->budi)->id,
            'meter_number' => 'MTR-DUSK-'.WaterMeter::count(), 'status' => 'active']);
        if ($history) MeterReading::create(['water_meter_id' => $meter->id, 'previous_value' => '300.00',
            'reading_value' => '317.00', 'reading_date' => now()->startOfMonth()->subDay()->toDateString(),
            'recorded_by' => $this->petugas->id, 'requires_review' => false]);
        return $meter;
    }

    protected function pending(): MeterReading
    {
        $meter = $this->meter();
        return MeterReading::create(['water_meter_id' => $meter->id, 'previous_value' => '317.00',
            'reading_value' => '342.00', 'reading_date' => now()->toDateString(),
            'recorded_by' => $this->petugas->id, 'notes' => 'Angka hasil pencatatan Petugas.', 'requires_review' => true]);
    }

    protected function evidence(Browser $browser, string $id): void
    {
        $browser->pause(max(0, (int) (getenv('DUSK_STEP_DELAY') ?: 650)))->screenshot($id);
    }

    protected function assertHttpStatus(Browser $browser, int $status): void
    {
        $browser->assertScript('performance.getEntriesByType("navigation")[0].responseStatus', $status);
    }
}
