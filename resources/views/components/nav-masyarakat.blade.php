<a href="{{ route('masyarakat.dashboard') }}"
   class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition
          {{ request()->routeIs('masyarakat.dashboard') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Dashboard
</a>
<a href="{{ route('masyarakat.bills.index') }}"
   class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition
          {{ request()->routeIs('masyarakat.bills.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Tagihan Air
</a>
<a href="{{ route('masyarakat.payments.history') }}"
   class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition
          {{ request()->routeIs('masyarakat.payments.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Riwayat Bayar
</a>
<a href="{{ route('masyarakat.complaints.index') }}"
   class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition
          {{ request()->routeIs('masyarakat.complaints.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Pengaduan
</a>

<a href="{{ route('masyarakat.meter-history.index') }}"
   class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">
    Riwayat Meter
</a>