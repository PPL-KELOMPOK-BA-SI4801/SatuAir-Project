<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Meter dan Tagihan</title>
</head>
<body>
    <h2>Riwayat Input Meter dan Tagihan</h2>

    <form method="GET" action="{{ route('riwayat-meter.index') }}">
        <label>Pelanggan:</label>
        <select name="pelanggan">
            <option value="">Semua Pelanggan</option>
            @foreach ($pelanggan as $item)
                <option value="{{ $item->id }}"
                    {{ request('pelanggan') == $item->id ? 'selected' : '' }}>
                    {{ $item->nama }}
                </option>
            @endforeach
        </select>

        <label>Periode:</label>
        <input type="date" name="periode" value="{{ request('periode') }}">

        <button type="submit">Cari</button>
        <a href="{{ route('riwayat-meter.index') }}">Reset</a>
    </form>

    <br>

    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>Kode Pelanggan</th>
                <th>Nama</th>
                <th>Periode</th>
                <th>Meter Awal</th>
                <th>Meter Akhir</th>
                <th>Pemakaian (m³)</th>
                <th>Total Tagihan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($riwayat as $item)
                <tr>
                    <td>{{ $item->kode_pelanggan }}</td>
                    <td>{{ $item->nama }}</td>
                    <td>{{ $item->periode }}</td>
                    <td>{{ $item->meter_awal }}</td>
                    <td>{{ $item->meter_akhir }}</td>
                    <td>{{ $item->pemakaian_m3 ?? '-' }}</td>
                    <td>{{ $item->total ?? '-' }}</td>
                    <td>
                        <a href="{{ route('riwayat-meter.show', $item->id) }}">
                            Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Data riwayat tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $riwayat->links() }}
</body>