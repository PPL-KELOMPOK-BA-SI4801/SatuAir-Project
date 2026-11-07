<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Riwayat Meter</title>
</head>
<body>
    <h2>Detail Riwayat Input Meter dan Tagihan</h2>

    <p>Kode Pelanggan: {{ $detail->kode_pelanggan }}</p>
    <p>Nama Pelanggan: {{ $detail->nama }}</p>
    <p>Alamat: {{ $detail->alamat }}</p>
    <p>Periode: {{ $detail->periode }}</p>
    <p>Meter Awal: {{ $detail->meter_awal }}</p>
    <p>Meter Akhir: {{ $detail->meter_akhir }}</p>
    <p>Pemakaian: {{ $detail->pemakaian_m3 ?? '-' }} m³</p>
    <p>Nomor Tagihan: {{ $detail->nomor_tagihan ?? '-' }}</p>
    <p>Total Tagihan: {{ $detail->total ?? '-' }}</p>
    <p>Jatuh Tempo: {{ $detail->jatuh_tempo ?? '-' }}</p>

    <a href="{{ route('riwayat-meter.index') }}">Kembali</a>
</body>
</html>