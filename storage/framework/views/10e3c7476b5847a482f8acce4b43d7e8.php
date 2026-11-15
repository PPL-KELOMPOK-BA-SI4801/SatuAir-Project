<a href="<?php echo e(route('petugas.dashboard')); ?>"
   class="block px-4 py-2.5 text-sm font-medium rounded-lg transition
          <?php echo e(request()->routeIs('petugas.dashboard') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10'); ?>">
    Dashboard
</a>
<a href="<?php echo e(route('petugas.meters.index')); ?>"
   class="block px-4 py-2.5 text-sm font-medium rounded-lg transition
          <?php echo e(request()->routeIs('petugas.meters.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10'); ?>">
    Catat Meter
</a>
<a href="<?php echo e(route('petugas.payments.index')); ?>"
   class="block px-4 py-2.5 text-sm font-medium rounded-lg transition
          <?php echo e(request()->routeIs('petugas.payments.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10'); ?>">
    Pembayaran
</a>
<a href="<?php echo e(route('petugas.complaints.index')); ?>"
   class="block px-4 py-2.5 text-sm font-medium rounded-lg transition
          <?php echo e(request()->routeIs('petugas.complaints.*') ? 'text-white bg-white/15' : 'text-white/70 hover:text-white hover:bg-white/10'); ?>">
    Pengaduan
</a>
<?php /**PATH C:\Users\WIN 11\Documents\SatuAir\SatuAir-Project\resources\views/components/nav-petugas-mobile.blade.php ENDPATH**/ ?>