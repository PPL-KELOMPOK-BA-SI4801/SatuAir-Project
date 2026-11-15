<?php $__env->startSection('title', 'Dashboard Petugas PAB'); ?>

<?php $__env->startSection('nav-items'); ?>
    <a href="<?php echo e(route('petugas.dashboard')); ?>" class="px-3 py-2 text-sm font-semibold text-white border-b-2 border-white">Dashboard Operasional</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    
    <div class="bg-gradient-to-r from-[#12345A] to-[#1976D2] rounded-2xl p-6 sm:p-8 text-white shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs font-medium text-blue-100 mb-2">
                <span>🔧</span> Petugas Unit Pengelola Air Bersih (PAB)
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Halo, <?php echo e($user->name); ?>!</h1>
            <p class="text-blue-100 text-sm sm:text-base mt-1">Area Operasional: Unit Pengelola Air Bersih (PAB)</p>
        </div>
        <div class="text-xs text-blue-100 bg-white/10 p-3 rounded-xl border border-white/10">
            <span>Tanggal Hari Ini:</span>
            <span class="block text-white font-bold text-sm mt-0.5"><?php echo e(now()->isoFormat('dddd, D MMMM Y')); ?></span>
        </div>
    </div>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Pencatatan Bulan Ini</p>
                <p class="text-2xl font-bold text-[#12345A] mt-1"><?php echo e($meterReadingsThisMonth); ?></p>
                <p class="text-xs text-gray-400 mt-1">Meteran terbaca</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#EAF4FF] text-[#1976D2] flex items-center justify-center text-xl">
                📟
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-700">Tagihan Tertunggak</p>
                <p class="text-2xl font-bold text-amber-600 mt-1"><?php echo e($unpaidBills); ?></p>
                <p class="text-xs text-amber-600/80 mt-1">Warga belum bayar</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                ⚠️
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-700">Verifikasi Pembayaran</p>
                <p class="text-2xl font-bold text-[#1976D2] mt-1"><?php echo e($pendingPayments); ?></p>
                <p class="text-xs text-blue-600/80 mt-1">Perlu dicek</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#1976D2] flex items-center justify-center text-xl">
                💳
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-rose-700">Pengaduan Aktif</p>
                <p class="text-2xl font-bold text-rose-600 mt-1"><?php echo e($activeComplaints); ?></p>
                <p class="text-xs text-rose-600/80 mt-1"><?php echo e($newComplaintsToday); ?> baru hari ini</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                🔔
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h2 class="text-base font-bold text-[#12345A] mb-2">Status Modul Operasional Petugas</h2>
        <p class="text-xs text-gray-500 mb-4 leading-relaxed">
            Sesuai arahan wawancara dengan BUMDes Karya Mandiri, fokus fase 1 adalah kemudahan pembayaran online dan pengaduan bagi masyarakat. Modul scan meteran air kamera OCR / pencatatan meter digital untuk petugas akan dihubungkan setelah validasi dosen pembimbing.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div class="p-4 rounded-xl bg-[#EAF4FF] border border-blue-200">
                <span class="font-bold text-[#1976D2] block text-sm">📷 Scan Meteran Air (Next Phase)</span>
                <p class="text-gray-600 mt-1">Petugas tinggal scan angka meteran pelanggan langsung keluar angka m³ tanpa catat manual di kertas.</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-gray-200">
                <span class="font-bold text-gray-800 block text-sm">✅ Verifikasi Bukti Online</span>
                <p class="text-gray-600 mt-1">Mencocokkan transfer bank / QRIS warga dengan mutasi rekening BUMDes.</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-gray-200">
                <span class="font-bold text-gray-800 block text-sm">🛠️ Respon Tiket Lapangan</span>
                <p class="text-gray-600 mt-1">Update status penanganan pipa bocor, debit mengecil, dan perbaikan fasilitas air.</p>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\WIN 11\Documents\SatuAir\SatuAir-Project\resources\views/petugas/dashboard.blade.php ENDPATH**/ ?>