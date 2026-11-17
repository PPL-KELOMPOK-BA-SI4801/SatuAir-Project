<a href="{{ route('masyarakat.dashboard') }}"
   class="block px-4 py-2.5 text-sm font-medium rounded-lg transition
          {{ request()->routeIs('masyarakat.dashboard') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Dashboard
</a>
<a href="{{ route('masyarakat.bills.index') }}"
   class="block px-4 py-2.5 text-sm font-medium rounded-lg transition
          {{ request()->routeIs('masyarakat.bills.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Tagihan Air
</a>
<a href="{{ route('masyarakat.payments.history') }}"
   class="block px-4 py-2.5 text-sm font-medium rounded-lg transition
          {{ request()->routeIs('masyarakat.payments.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Riwayat Bayar
</a>
<a href="{{ route('masyarakat.complaints.index') }}"
   class="block px-4 py-2.5 text-sm font-medium rounded-lg transition
          {{ request()->routeIs('masyarakat.complaints.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
    Pengaduan
</a>

