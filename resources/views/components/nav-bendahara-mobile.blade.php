<a href="{{ route('bendahara.dashboard') }}"
   class="block px-4 py-2.5 text-sm font-medium rounded-lg transition
          {{ request()->routeIs('bendahara.dashboard') || request()->routeIs('admin.dashboard') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Dashboard
</a>
<a href="{{ route('bendahara.rekap.index') }}"
   class="block px-4 py-2.5 text-sm font-medium rounded-lg transition
          {{ request()->routeIs('bendahara.rekap.*') || request()->routeIs('admin.rekap.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Rekap
</a>
<a href="{{ route('bendahara.pengguna.index') }}"
   class="block px-4 py-2.5 text-sm font-medium rounded-lg transition
          {{ request()->routeIs('bendahara.pengguna.*') || request()->routeIs('admin.pengguna.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Pengguna
</a>
<a href="{{ route('bendahara.complaints.index') }}"
   class="block px-4 py-2.5 text-sm font-medium rounded-lg transition
          {{ request()->routeIs('bendahara.complaints.*') || request()->routeIs('admin.complaints.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Pengaduan
</a>
