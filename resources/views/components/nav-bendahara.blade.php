<a href="{{ route('bendahara.dashboard') }}"
   class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition
          {{ request()->routeIs('bendahara.dashboard') || request()->routeIs('admin.dashboard') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Dashboard
</a>
<a href="{{ route('bendahara.rekap.index') }}"
   class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition
          {{ request()->routeIs('bendahara.rekap.*') || request()->routeIs('admin.rekap.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Rekap
</a>
<a href="{{ route('bendahara.pengguna.index') }}"
   class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition
          {{ request()->routeIs('bendahara.pengguna.*') || request()->routeIs('admin.pengguna.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Pengguna
</a>
<a href="{{ route('bendahara.complaints.index') }}"
   class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition
          {{ request()->routeIs('bendahara.complaints.*') || request()->routeIs('admin.complaints.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Pengaduan
</a>
