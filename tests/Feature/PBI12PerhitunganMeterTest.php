<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WaterMeter;
use App\Models\MeterReading;
use App\Models\WaterBill;
use App\Models\BillingPeriod;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class PBI12PerhitunganMeterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function createPetugas(): User
    {
        return User::factory()->create([
            'role' => 'petugas',
            'status' => 'active',
        ]);
    }

    private function createCustomer(): User
    {
        return User::factory()->create([
            'role' => 'masyarakat',
            'status' => 'active',
        ]);
    }

    private function createActivePeriod(): BillingPeriod
    {
        return BillingPeriod::query()
            ->where('status', 'active')
            ->firstOrFail();
    }

    public function test_petugas_can_save_valid_meter_reading(): void
    {
        $petugas = $this->createPetugas();
        $customer = $this->createCustomer();

        $meter = WaterMeter::create([
            'user_id' => $customer->id,
            'meter_number' => 'MTR-TEST01',
            'status' => 'active',
        ]);

        $period = $this->createActivePeriod();

        $date = Carbon::create(
            $period->year,
            $period->month,
            10
        );

        if ($date->isFuture()) {
            $date = now();
        }

        $response = $this->actingAs($petugas)
            ->post(route('petugas.meters.store'), [
                'water_meter_id' => $meter->id,
                'reading_value' => 120,
                'reading_date' => $date->toDateString(),
                'notes' => 'Pembacaan meter valid',
            ]);

        $response->assertRedirect(route('petugas.meters.index'));

        $this->assertDatabaseHas('meter_readings', [
            'water_meter_id' => $meter->id,
            'reading_value' => 120,
            'recorded_by' => $petugas->id,
        ]);
    }

    public function test_meter_reading_cannot_be_negative(): void
    {
        $petugas = $this->createPetugas();
        $customer = $this->createCustomer();

        $meter = WaterMeter::create([
            'user_id' => $customer->id,
            'meter_number' => 'MTR-TEST02',
            'status' => 'active',
        ]);

        $response = $this->actingAs($petugas)
            ->post(route('petugas.meters.store'), [
                'water_meter_id' => $meter->id,
                'reading_value' => -10,
                'reading_date' => now()->toDateString(),
            ]);

        $response->assertSessionHasErrors('reading_value');

        $this->assertDatabaseMissing('meter_readings', [
            'water_meter_id' => $meter->id,
        ]);
    }

    public function test_meter_reading_cannot_be_less_than_previous_reading(): void
    {
        $petugas = $this->createPetugas();
        $customer = $this->createCustomer();

        $meter = WaterMeter::create([
            'user_id' => $customer->id,
            'meter_number' => 'MTR-TEST03',
            'status' => 'active',
        ]);

        $period = $this->createActivePeriod();

        $previousDate = Carbon::create(
            $period->year,
            $period->month,
            5
        );

        MeterReading::create([
            'water_meter_id' => $meter->id,
            'reading_value' => 100,
            'previous_value' => 80,
            'reading_date' => $previousDate->toDateString(),
            'recorded_by' => $petugas->id,
        ]);

        $readingDate = Carbon::create(
            $period->year,
            $period->month,
            10
        );

        if ($readingDate->isFuture()) {
            $readingDate = now();
        }

        $response = $this->actingAs($petugas)
            ->post(route('petugas.meters.store'), [
                'water_meter_id' => $meter->id,
                'reading_value' => 90,
                'reading_date' => $readingDate->toDateString(),
            ]);

        $response->assertSessionHasErrors('reading_value');
    }

    public function test_meter_reading_cannot_use_future_date(): void
    {
        $petugas = $this->createPetugas();
        $customer = $this->createCustomer();

        $meter = WaterMeter::create([
            'user_id' => $customer->id,
            'meter_number' => 'MTR-TEST04',
            'status' => 'active',
        ]);

        $response = $this->actingAs($petugas)
            ->post(route('petugas.meters.store'), [
                'water_meter_id' => $meter->id,
                'reading_value' => 100,
                'reading_date' => now()->addDay()->toDateString(),
            ]);

        $response->assertSessionHasErrors('reading_date');
    }

    public function test_billing_service_calculates_correct_bill(): void
    {
        $customer = $this->createCustomer();

        $meter = WaterMeter::create([
            'user_id' => $customer->id,
            'meter_number' => 'MTR-TEST05',
            'status' => 'active',
        ]);

        $period = $this->createActivePeriod();

        $readingDate = Carbon::create(
            $period->year,
            $period->month,
            10
        );

        if ($readingDate->isFuture()) {
            $readingDate = now();
        }

        $reading = MeterReading::create([
            'water_meter_id' => $meter->id,
            'reading_value' => 120,
            'previous_value' => 100,
            'reading_date' => $readingDate->toDateString(),
        ]);

        $bill = app(BillingService::class)
            ->createBillFromReading($reading, $customer->id);

        $this->assertNotNull($bill);
        $this->assertEquals(20, (float) $bill->usage_m3);
        $this->assertEquals(5000, (float) $bill->rate_per_m3);
        $this->assertEquals(2500, (float) $bill->admin_fee);
        $this->assertEquals(102500, (float) $bill->total_amount);
    }

    public function test_billing_service_does_not_create_duplicate_bill(): void
    {
        $customer = $this->createCustomer();

        $meter = WaterMeter::create([
            'user_id' => $customer->id,
            'meter_number' => 'MTR-TEST06',
            'status' => 'active',
        ]);

        $period = $this->createActivePeriod();

        $readingDate = Carbon::create(
            $period->year,
            $period->month,
            10
        );

        if ($readingDate->isFuture()) {
            $readingDate = now();
        }

        $reading = MeterReading::create([
            'water_meter_id' => $meter->id,
            'reading_value' => 120,
            'previous_value' => 100,
            'reading_date' => $readingDate->toDateString(),
        ]);

        $service = app(BillingService::class);

        $firstBill = $service->createBillFromReading(
            $reading,
            $customer->id
        );

        $secondBill = $service->createBillFromReading(
            $reading,
            $customer->id
        );

        $this->assertEquals($firstBill->id, $secondBill->id);

        $this->assertDatabaseHas('water_bills', [
            'id' => $firstBill->id,
            'water_meter_id' => $meter->id,
        ]);

        $this->assertEquals($firstBill->id, $secondBill->id);
    }
}
