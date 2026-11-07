<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class HitungPemakaianMeter
{
    /**
     * Ubah input meter akhir menjadi bilangan bulat dalam satuan 0,01 m³.
     */
    public function angkaKeSatuan(mixed $angka): int
    {
        if (! is_string($angka) && ! is_int($angka) && ! is_float($angka)) {
            $this->gagal('Meter akhir wajib berupa angka.');
        }

        $angka = str_replace(',', '.', trim((string) $angka));

        if (! preg_match('/^\d{1,9}(?:\.\d{1,2})?$/D', $angka)) {
            $this->gagal('Isi meter akhir dengan angka positif atau nol, maksimal 9 digit dan 2 desimal.');
        }

        [$utuh, $desimal] = array_pad(explode('.', $angka, 2), 2, '');

        return ((int) $utuh * 100) + (int) str_pad($desimal, 2, '0');
    }

    /**
     * Terima kedua angka meter dan kembalikan pemakaian dalam satuan 0,01 m³.
     */
    public function hitung(int $meterAwal, int $meterAkhir): int
    {
        if ($meterAwal < 0 || $meterAkhir < $meterAwal) {
            $this->gagal('Meter akhir tidak boleh lebih kecil dari meter awal.');
        }

        return $meterAkhir - $meterAwal;
    }

    public static function format(int $satuan): string
    {
        return number_format($satuan / 100, 2, ',', '.');
    }

    private function gagal(string $pesan): never
    {
        throw ValidationException::withMessages(['meter_akhir' => $pesan]);
    }
}
