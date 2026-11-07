<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — SatuAir</title>
    <link rel="stylesheet" href="{{ asset('assets/petugas.css') }}">
    <script src="{{ asset('assets/meter.js') }}" defer></script>
</head>
<body>
<header class="topbar">
    <a class="brand" href="{{ route('meter.index') }}" aria-label="SatuAir beranda">
        <span class="brand-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C9 6 4 10 4 15a8 8 0 0 0 16 0c0-5-5-9-8-13Z" fill="currentColor"/></svg></span>
        <span><b>Satu</b>Air</span>
    </a>
    <nav aria-label="Menu Petugas">
        <span class="nav-item unavailable" aria-disabled="true">Dashboard</span>
        <a class="nav-item active" href="{{ route('meter.index') }}" aria-current="page">Catat Meter</a>
        <span class="nav-item unavailable" aria-disabled="true">Pembayaran</span>
        <span class="nav-item unavailable" aria-disabled="true">Pengaduan</span>
    </nav>
    <div class="profile"><svg class="bell" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 8 3 8 3 9H3c0-1 3-1 3-9Zm4 12h4M12 1v2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg><span class="avatar">A</span><span>Ahmad Fauzi (Petugas PAB)</span><span aria-hidden="true">⌄</span></div>
</header>
<main>
    @yield('content')
</main>
<footer>SatuAir © 2026 — BUMDes Karya Mandiri</footer>
</body>
</html>
