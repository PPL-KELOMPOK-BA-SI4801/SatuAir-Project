<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $editable ? 'Edit' : 'Detail' }} Pelanggan · SatuAir</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; flex-direction: column; background: #f3f6fa; color: #172b46; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .topbar { height: 96px; background: #112b4b; color: #e8eef6; display: flex; align-items: center; padding: 0 32px; gap: 24px; }
        .brand { display: flex; align-items: center; gap: 16px; color: white; font-size: 28px; font-weight: 700; text-decoration: none; white-space: nowrap; }
        .brand-mark { display:grid; place-items:center; width:50px; height:50px; border-radius:50%; background:#1877f2; }
        .brand-mark svg { width:25px; height:25px; fill:white; }
        .nav { display:flex; align-items:center; justify-content:center; gap:12px; flex:1; }
        .nav a { color:#d5deeb; text-decoration:none; font-size:18px; padding:14px 22px; border-radius:10px; }
        .nav a.active { color:#fff; background:#203e62; font-weight:600; }
        .account { display:flex; align-items:center; gap:13px; white-space:nowrap; font-size:17px; }
        .bell { width:25px; height:25px; margin-right:22px; color:#cfdaea; }
        .avatar { display:grid; place-items:center; width:44px; height:44px; border-radius:50%; background:#078cc4; color:white; font-weight:700; }
        main { flex:1; width:min(736px, calc(100% - 32px)); margin:48px auto 72px; }
        .back { display:inline-flex; align-items:center; gap:12px; margin:0 0 40px; color:#4b5c73; text-decoration:none; font-size:17px; }
        .back svg { width:20px; height:20px; }
        .card { background:white; border-radius:28px; padding:52px 50px 50px; box-shadow:0 8px 30px rgba(24,48,77,.06); }
        .card-head { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:26px; }
        h1 { margin:0; font-size:28px; letter-spacing:-.7px; }
        .badge { padding:8px 17px; border-radius:999px; color:#178448; background:#e6f7ef; font-weight:600; }
        .customer { margin:0 0 5px; font-size:20px; font-weight:700; color:#2b374a; }
        .email { margin:0 0 40px; color:#9babc0; font-size:17px; }
        .field { margin-bottom:37px; }
        label { display:block; margin-bottom:12px; color:#43536a; font-size:17px; }
        input { width:100%; height:61px; border:1.5px solid #dce5f0; border-radius:11px; padding:0 22px; color:#46546a; font:inherit; font-size:19px; outline:none; }
        input:focus { border-color:#2575d0; box-shadow:0 0 0 3px rgba(37,117,208,.12); }
        input:disabled { background:#f8fafc; color:#5c697c; }
        .unit-wrap { position:relative; }
        .unit-wrap input { padding-right:60px; }
        .unit { position:absolute; right:22px; top:50%; transform:translateY(-50%); color:#9babc0; pointer-events:none; }
        .hint { margin:12px 0 0; color:#9babc0; font-size:16px; }
        .divider { border:0; border-top:1px solid #edf1f5; margin:32px 0 42px; }
        .actions { display:flex; gap:12px; }
        .button { display:flex; justify-content:center; align-items:center; width:100%; min-height:65px; border:0; border-radius:11px; background:#122f52; color:white; text-decoration:none; font:inherit; font-size:18px; cursor:pointer; }
        .button.secondary { background:#eef3f8; color:#263c58; }
        footer { min-height:70px; border-top:1px solid #e9eef4; display:grid; place-items:center; color:#8292a7; font-size:15px; }
        @media (max-width: 900px) { .topbar { height:auto; min-height:78px; padding:14px 20px; flex-wrap:wrap; } .brand { font-size:23px; } .brand-mark { width:42px; height:42px; } .nav { order:3; flex-basis:100%; overflow:auto; justify-content:flex-start; } .nav a { font-size:15px; padding:10px 14px; } .account { margin-left:auto; font-size:14px; } .bell { margin-right:4px; } }
        @media (max-width: 560px) { main { margin:28px auto 48px; } .back { margin-bottom:24px; font-size:15px; } .card { border-radius:20px; padding:30px 22px; } h1 { font-size:23px; } .badge { font-size:13px; padding:7px 12px; } .customer { font-size:18px; } .email { font-size:15px; } label { font-size:15px; } input { height:54px; font-size:17px; } .actions { flex-direction:column; } .account .account-name { display:none; } }
    </style>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('home') }}" aria-label="SatuAir">
            <span class="brand-mark"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5C9.1 6.7 4.5 11 4.5 15.3a7.5 7.5 0 0 0 15 0C19.5 11 14.9 6.7 12 2.5Z"/></svg></span>
            <span>SatuAir</span>
        </a>
        <nav class="nav" aria-label="Navigasi utama">
            <a href="#dashboard">Dashboard</a><a href="#rekap">Rekap</a><a class="active" href="{{ route('pelanggan.show', $id) }}">Pengguna</a><a href="#pengaduan">Pengaduan</a>
        </nav>
        <div class="account">
            <svg class="bell" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-label="Notifikasi"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span class="avatar">P</span><span class="account-name">Pak Hendra (Pengurus BUMDes)</span><span aria-hidden="true">⌄</span>
        </div>
    </header>

    <main>
        <a class="back" href="{{ route('pelanggan.show', $id) }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6M9 12h12" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Kembali ke Manajemen Pengguna
        </a>
        <section class="card" aria-labelledby="page-title">
            <div class="card-head"><h1 id="page-title">{{ $editable ? 'Edit Pengguna' : 'Detail Pengguna' }}</h1><span class="badge">Masyarakat</span></div>
            <p class="customer">Budi Santoso</p>
            <p class="email">Email: budi@gmail.com</p>
            <form action="#" method="post">
                <div class="field">
                    <label for="meter">Pembacaan Meter Terbaru (m³)</label>
                    <div class="unit-wrap"><input id="meter" name="meter" type="number" step="0.01" value="317.00" @disabled(!$editable)><span class="unit">m³</span></div>
                    <p class="hint">Pembacaan sebelumnya: 317.00 m³</p>
                </div>
                <div class="field">
                    <label for="tarif">Tarif per m³ (Rp)</label>
                    <input id="tarif" name="tarif" type="number" step="0.01" value="0.00" @disabled(!$editable)>
                </div>
                <hr class="divider">
                @if ($editable)
                    <div class="actions"><a class="button secondary" href="{{ route('pelanggan.show', $id) }}">Batal</a><button class="button" type="button">Simpan Perubahan</button></div>
                @else
                    <a class="button" href="{{ route('pelanggan.edit', $id) }}">Edit Data Pelanggan</a>
                @endif
            </form>
        </section>
    </main>
    <footer>SatuAir © 2026 — BUMDes Karya Mandiri</footer>
</body>
</html>
