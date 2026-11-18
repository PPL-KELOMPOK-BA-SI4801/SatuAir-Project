@if (auth()->user()?->hasPermission('dashboard.bendahara'))
<a href="{{ route('bendahara.dashboard') }}"
   class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition
          {{ request()->routeIs('bendahara.dashboard') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Dashboard
</a>
@endif
@if (auth()->user()?->hasPermission('meter.review'))
<a href="{{ route('bendahara.reviews.index') }}"
   class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition {{ request()->routeIs('bendahara.reviews.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">Pemeriksaan</a>
@endif
@if (auth()->user()?->hasPermission('finance.manage'))
<a href="{{ route('bendahara.rekap.index') }}"
   class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition
          {{ request()->routeIs('bendahara.rekap.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Rekap
</a>
@endif
@if (auth()->user()?->hasPermission('field.manage'))
<a href="{{ route('bendahara.complaints.index') }}"
   class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition
          {{ request()->routeIs('bendahara.complaints.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Pengaduan
</a>
@endif
@if (auth()->user()?->hasPermission('users.manage'))
<a href="{{ route('bendahara.pengguna.index') }}"
   class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition
          {{ request()->routeIs('bendahara.pengguna.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Pengguna
</a>
@endif
@if (auth()->user()?->hasPermission('access.manage'))
<a href="{{ route('bendahara.access.index') }}"
   class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition
          {{ request()->routeIs('bendahara.access.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Hak Akses
</a>
@endif
