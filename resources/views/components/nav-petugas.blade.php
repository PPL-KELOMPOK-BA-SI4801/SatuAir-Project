@if (auth()->user()?->hasPermission('field.manage'))
<a href="{{ route('petugas.dashboard') }}"
   class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition
          {{ request()->routeIs('petugas.dashboard') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Dashboard
</a>
<a href="{{ route('petugas.meters.index') }}"
   class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition
          {{ request()->routeIs('petugas.meters.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Catat Meter
</a>
@endif
@if (auth()->user()?->hasPermission('payments.view'))
<a href="{{ route('petugas.payments.index') }}"
   class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition
          {{ request()->routeIs('petugas.payments.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Pembayaran
</a>
@endif
@if (auth()->user()?->hasPermission('field.manage'))
<a href="{{ route('petugas.complaints.index') }}"
   class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition
          {{ request()->routeIs('petugas.complaints.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Pengaduan
</a>
@endif
@if (auth()->user()?->hasPermission('finance.manage'))
<a href="{{ route('bendahara.rekap.index') }}" class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition {{ request()->routeIs('bendahara.rekap.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">Rekap Keuangan</a>
<a href="{{ route('bendahara.pembayaran') }}" class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition {{ request()->routeIs('bendahara.pembayaran*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">Pembayaran</a>
@endif
@if (auth()->user()?->hasPermission('meter.review'))
<a href="{{ route('bendahara.reviews.index') }}" class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition {{ request()->routeIs('bendahara.reviews.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">Pemeriksaan</a>
@endif
@if (auth()->user()?->hasPermission('users.manage'))
<a href="{{ route('bendahara.pengguna.index') }}" class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition {{ request()->routeIs('bendahara.pengguna.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">Pengguna</a>
@endif
@if (auth()->user()?->hasPermission('access.manage'))
<a href="{{ route('bendahara.access.index') }}" class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition {{ request()->routeIs('bendahara.access.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">Hak Akses</a>
@endif
