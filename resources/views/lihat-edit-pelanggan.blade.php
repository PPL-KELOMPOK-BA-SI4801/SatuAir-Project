<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $editable ? 'Edit' : 'Detail' }} Pelanggan · SatuAir</title>
    <style>
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; display:flex; flex-direction:column; background:#f5f7fa; color:#263445; font-family:Inter,Arial,sans-serif; }
        .topbar { min-height:56px; background:#12345a; color:#e8eef6; display:flex; align-items:center; padding:0 20px; gap:34px; }
        .brand { color:white; text-decoration:none; font-size:16px; font-weight:700; white-space:nowrap; }
        .brand-mark { display:inline-grid; place-items:center; width:28px; height:28px; margin-right:8px; border-radius:50%; background:#1683e5; }
        nav { display:flex; align-items:center; gap:8px; flex:1; }
        nav a { color:#d5deeb; text-decoration:none; font-size:13px; padding:9px 12px; border-radius:8px; }
        nav a.active { background:#203e62; color:#fff; font-weight:600; }
        .account { font-size:12px; white-space:nowrap; }
        main { flex:1; width:min(900px,calc(100% - 32px)); margin:32px auto 56px; }
        .back { display:inline-block; margin:0 0 20px; color:#50627a; font-size:13px; text-decoration:none; }
        .card { background:#fff; border:1px solid #e8edf3; border-radius:14px; padding:28px; box-shadow:0 3px 12px #12345a0a; }
        .card-head { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:20px; }
        h1 { margin:0; color:#12345a; font-size:22px; }
        .badge { padding:5px 10px; border-radius:5px; background:#dcfce7; color:#15803d; font-size:11px; font-weight:700; text-transform:uppercase; }
        .flash { margin:0 0 18px; padding:12px 14px; border:1px solid #bbf7d0; border-radius:8px; background:#f0fdf4; color:#166534; font-size:13px; }
        .errors { margin:0 0 18px; padding:12px 14px; border:1px solid #fecaca; border-radius:8px; background:#fef2f2; color:#b91c1c; font-size:13px; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:18px 20px; }
        .field { min-width:0; }
        .field.full { grid-column:1/-1; }
        label { display:block; margin-bottom:7px; color:#475569; font-size:12px; font-weight:600; }
        input,textarea { display:block; width:100%; border:1px solid #d7e0ea; border-radius:8px; padding:10px 12px; color:#263445; font:inherit; font-size:13px; background:white; }
        input:focus,textarea:focus { outline:2px solid #bfdbfe; border-color:#1976d2; }
        input:disabled,textarea:disabled { background:#f8fafc; color:#64748b; }
        textarea { min-height:94px; resize:vertical; }
        .readonly { padding:10px 12px; border:1px solid #e2e8f0; border-radius:8px; background:#f8fafc; color:#64748b; font-size:13px; }
        .hint { margin:6px 0 0; color:#64748b; font-size:11px; }
        .actions { display:flex; justify-content:flex-end; gap:10px; margin-top:24px; padding-top:20px; border-top:1px solid #edf1f5; }
        .button { display:inline-flex; justify-content:center; align-items:center; min-height:38px; border:1px solid transparent; border-radius:8px; padding:0 16px; background:#1976d2; color:white; text-decoration:none; font:inherit; font-size:13px; font-weight:600; cursor:pointer; }
        .button:hover { background:#1565c0; }
        .button.secondary { border-color:#d7e0ea; background:#fff; color:#40536b; }
        footer { min-height:58px; border-top:1px solid #e9eef4; display:grid; place-items:center; background:white; color:#8292a7; font-size:12px; }
        @media(max-width:620px) { .topbar { flex-wrap:wrap; padding:12px 16px; gap:8px; } nav { order:3; flex-basis:100%; overflow:auto; } .account { margin-left:auto; } .grid { grid-template-columns:1fr; } .field.full { grid-column:auto; } .card { padding:20px; } }
    </style>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('home') }}"><span class="brand-mark">◆</span>SatuAir</a>
        <nav aria-label="Navigasi utama">
            <a href="{{ route('bendahara.dashboard') }}">Dashboard</a>
            @unless($ownProfile ?? false)<a href="{{ route('bendahara.pengguna.index') }}" class="active">Pengguna</a>@endunless
        </nav>
        <a class="account" href="{{ route('bendahara.profile.edit') }}">{{ auth()->user()->name }}</a>
    </header>

    <main>
        @unless($ownProfile ?? false)<a class="back" href="{{ route('bendahara.pengguna.index') }}">Kembali ke Manajemen Pengguna</a>@endunless
        <section class="card" aria-labelledby="page-title">
            @if(session('success')) <p class="flash" role="status">{{ session('success') }}</p> @endif
            @if($errors->any())
                <div class="errors" role="alert"><strong>Periksa kembali data berikut:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <div class="card-head">
                <h1 id="page-title">{{ ($ownProfile ?? false) ? 'Profil Saya' : ($editable ? 'Edit Data Pelanggan' : 'Detail Pelanggan') }}</h1>
                <span class="badge">{{ ucfirst($customer->role) }}</span>
            </div>
            @if($editable)<form action="{{ ($ownProfile ?? false) ? route('bendahara.profile.update') : route('bendahara.pengguna.profile.update', $customer) }}" method="post">
                @csrf
                @method('PUT')
            @endif
                <div class="grid">
                    <div class="field">
                        <label for="name">Nama lengkap</label>
                        <input id="name" name="name" value="{{ old('name', $customer->name) }}" @disabled(!$editable) required maxlength="255" autocomplete="name">
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $customer->email) }}" @disabled(!$editable) required maxlength="255" autocomplete="email">
                    </div>
                    <div class="field">
                        <label for="phone">Nomor telepon</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', $customer->phone) }}" @disabled(!$editable) maxlength="30" autocomplete="tel">
                    </div>
                    <div class="field">
                        <label>ID pelanggan</label>
                        <div class="readonly">{{ $customer->customer_id ?: (($customer->role === 'bendahara' ? 'ADM-' : 'CUST-') . str_pad((string) $customer->id, 5, '0', STR_PAD_LEFT)) }}</div>
                        <p class="hint">ID pelanggan tidak dapat diubah.</p>
                    </div>
                    <div class="field full">
                        <label for="address">Alamat</label>
                        <textarea id="address" name="address" @disabled(!$editable) maxlength="1000" autocomplete="street-address">{{ old('address', $customer->address) }}</textarea>
                    </div>
                </div>
                <div class="actions">
                    @if($editable)
                        @if($ownProfile ?? false)
                            <a class="button secondary" href="{{ route('bendahara.dashboard') }}">Batal</a>
                        @else
                            <a class="button secondary" href="{{ route('bendahara.pengguna.show', $customer) }}">Batal</a>
                        @endif
                        <button class="button" type="submit">Simpan Perubahan</button>
                    @else
                        <a class="button secondary" href="{{ route('bendahara.pengguna.index') }}">Kembali</a>
                        <a class="button" href="{{ route('bendahara.pengguna.profile.edit', $customer) }}">Edit Data Pelanggan</a>
                    @endif
                </div>
            @if($editable)</form>@endif
        </section>
    </main>
    <footer>SatuAir &copy; {{ date('Y') }} — BUMDes Karya Mandiri</footer>
</body>
</html>
