<?php $__env->startSection('title', 'Riwayat Meter'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto space-y-6">

    
    <div>
        <h1 class="text-2xl font-bold text-[#12345A]">Riwayat Meter</h1>
        <p class="text-sm text-gray-500 mt-1">
            Pantau riwayat penggunaan air berdasarkan pencatatan meter Anda.
        </p>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Total Pencatatan</p>
            <h2 class="text-2xl font-bold text-[#12345A] mt-2">
                <?php echo e($totalRecords); ?>

            </h2>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Total Penggunaan Air</p>
            <h2 class="text-2xl font-bold text-blue-600 mt-2">
                <?php echo e(number_format($totalUsage, 2, ',', '.')); ?> m³
            </h2>
        </div>
    </div>

    
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <form method="GET" action="<?php echo e(route('masyarakat.meter-history.index')); ?>"
              class="grid grid-cols-1 md:grid-cols-4 gap-3">

            <input type="date" name="search"
                   value="<?php echo e(request('search')); ?>"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm">

            <select name="month"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Semua Bulan</option>
                <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($month); ?>"
                        <?php if(request('month') == $month): echo 'selected'; endif; ?>>
                        <?php echo e(\Carbon\Carbon::create()->month($month)->translatedFormat('F')); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <select name="year"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Semua Tahun</option>
                <?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($year); ?>"
                        <?php if(request('year') == $year): echo 'selected'; endif; ?>>
                        <?php echo e($year); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <div class="flex gap-2">
                <button type="submit"
                        class="bg-[#12345A] text-white rounded-lg px-4 py-2 text-sm">
                    Cari
                </button>

                <a href="<?php echo e(route('masyarakat.meter-history.index')); ?>"
                   class="border border-gray-300 rounded-lg px-4 py-2 text-sm">
                    Reset
                </a>
            </div>
        </form>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h2 class="font-bold text-[#12345A]">Daftar Riwayat Pencatatan</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Nomor Meter</th>
                        <th class="px-5 py-3">Meter Sebelumnya</th>
                        <th class="px-5 py-3">Meter Saat Ini</th>
                        <th class="px-5 py-3">Penggunaan</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $readings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reading): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-5 py-4">
                                <?php echo e($reading->reading_date->format('d M Y')); ?>

                            </td>

                            <td class="px-5 py-4">
                                <?php echo e($reading->waterMeter->meter_number ?? '-'); ?>

                            </td>

                            <td class="px-5 py-4">
                                <?php echo e($reading->previous_value ?? '-'); ?>

                            </td>

                            <td class="px-5 py-4">
                                <?php echo e($reading->reading_value); ?>

                            </td>

                            <td class="px-5 py-4 font-semibold text-blue-600">
                                <?php echo e($reading->previous_value !== null
                                    ? number_format($reading->reading_value - $reading->previous_value, 2, ',', '.') . ' m³'
                                    : '-'); ?>

                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-500">
                                Belum ada riwayat pencatatan meter.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-gray-100">
            <?php echo e($readings->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/niswahzilfatiljannah/SatuAir-Project/resources/views/masyarakat/meter-history/index.blade.php ENDPATH**/ ?>