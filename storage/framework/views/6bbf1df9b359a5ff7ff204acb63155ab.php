<?php $__env->startSection('title', 'Catat Meter Pelanggan'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6 py-6">
    
    
    <div>
        <a href="<?php echo e(route('petugas.meters.index')); ?>" class="text-sm font-medium text-blue-600 hover:underline inline-flex items-center gap-1">
            ← Kembali ke jadwal pencatatan
        </a>
    </div>

    
    <div>
        <h1 class="text-2xl font-bold text-[#12345A]">Catat Meter Pelanggan</h1>
        <p class="text-sm text-gray-500 mt-1">Masukkan angka meter periode Oktober 2026. Pemakaian dihitung otomatis.</p>
    </div>

    
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        
        
        <?php if($errors->any()): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
                <ul class="list-disc list-inside space-y-1">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('petugas.meters.store')); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="water_meter_id" value="<?php echo e($meter->id); ?>">
            <input type="hidden" name="reading_date" value="<?php echo e($defaultReadingDate); ?>">

            
            <div class="bg-slate-50/80 border border-slate-100 rounded-2xl p-5 grid grid-cols-1 sm:grid-cols-3 gap-y-4 gap-x-6 text-sm">
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Pelanggan</span>
                    <span class="font-bold text-[#12345A]"><?php echo e($meter->user->name ?? '-'); ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">ID Pelanggan</span>
                    <span class="font-bold text-[#12345A]"><?php echo e($meter->user->customer_id ?? '-'); ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">No. Meter</span>
                    <span class="font-bold font-mono text-[#12345A]"><?php echo e($meter->meter_number); ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Wilayah</span>
                    <span class="font-bold text-[#12345A]"><?php echo e($meter->area->name ?? '-'); ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Jadwal Catat</span>
                    <span class="font-bold text-[#12345A]">Sab, 31 Okt 2026</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Status</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">
                        BELUM DICATAT
                    </span>
                </div>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                
                <div class="bg-white border border-gray-200 rounded-2xl p-4 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Meter awal</span>
                        <div class="text-2xl font-bold text-[#12345A] mt-1">
                            <?php echo e($previousReading ? number_format($previousReading->reading_value, 2, ',', '.') : '0.00'); ?> <span class="text-sm font-normal text-gray-500">m³</span>
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-3">
                        <?php if($previousReading): ?>
                            Otomatis dari pencatatan <?php echo e(\Carbon\Carbon::parse($previousReading->reading_date)->format('d M Y')); ?>.
                        <?php else: ?>
                            Belum ada pencatatan sebelumnya.
                        <?php endif; ?>
                    </p>
                </div>

                
                <div class="bg-white border-2 border-blue-500 rounded-2xl p-4 flex flex-col justify-between shadow-sm">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Meter akhir</label>
                        <div class="relative flex items-center">
                            <input type="number" step="0.01" name="reading_value" id="reading_value" value="<?php echo e(old('reading_value')); ?>" placeholder="0.00" required
                                   class="w-full text-xl font-bold text-[#12345A] border-0 border-b border-gray-300 focus:border-blue-600 focus:ring-0 p-0 pb-1 bg-transparent">
                            <span class="text-sm font-normal text-gray-500 ml-2">m³</span>
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-3">Masukkan angka meter saat ini.</p>
                </div>

                
                <div class="bg-slate-50 border border-gray-200 rounded-2xl p-4 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Pemakaian</span>
                        <div class="text-2xl font-bold text-[#12345A] mt-1" id="pemakaian_text">
                            0.00 <span class="text-sm font-normal text-gray-500">m³</span>
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-3">Meter akhir dikurangi meter awal.</p>
                </div>
            </div>

            
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700">Foto bukti meter <span class="text-red-500">wajib</span></label>
                <div class="border-2 border-dashed border-gray-200 rounded-2xl p-6 text-center hover:border-blue-400 transition bg-slate-50/50">
                    <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png,image/jpg" class="hidden" required>
                    
                    
                    <div id="uploadDefault" class="flex flex-col items-center justify-center space-y-2">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                            📷
                        </div>
                        <div class="text-sm text-gray-600">
                            <label for="photoInput" class="text-blue-600 font-semibold cursor-pointer hover:underline">Unggah foto</label> atau seret kesini
                        </div>
                        <p class="text-xs text-gray-400">Pastikan angka pada meteran terlihat jelas. Format JPG atau PNG, maksimal 5MB.</p>
                    </div>

                    
                    <div id="uploadPreview" class="hidden flex items-center justify-between bg-white border border-gray-200 p-4 rounded-xl text-left">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">IMG</div>
                            <div>
                                <p id="fileName" class="text-sm font-semibold text-gray-800 truncate max-w-xs">file.jpg</p>
                                <p class="text-xs text-gray-400">Berhasil dipilih dan siap diunggah</p>
                            </div>
                        </div>
                        <button type="button" id="removePhoto" class="px-3 py-1 bg-red-50 text-red-600 hover:bg-red-100 text-xs font-semibold rounded-lg transition">Hapus</button>
                    </div>
                </div>
            </div>

            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Catatan (Opsional)</label>
                <textarea name="notes" rows="3" placeholder="Tambahkan catatan jika diperlukan..."
                          class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm p-3.5"><?php echo e(old('notes')); ?></textarea>
            </div>

            
            <div class="bg-slate-50 border border-gray-200 rounded-xl p-4 text-xs text-gray-600">
                Meter akhir tidak boleh lebih kecil dari meter awal (<span class="font-semibold"><?php echo e($previousReading ? number_format($previousReading->reading_value, 2, ',', '.') : '0.00'); ?> m³</span>). Pelanggan hanya dapat dicatat satu kali per periode, dan foto bukti meter wajib dilampirkan.
            </div>

            
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="flex-1 py-3 bg-[#1976D2] hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-md transition text-center">
                    Simpan pencatatan
                </button>
                <a href="<?php echo e(route('petugas.meters.index')); ?>" class="px-6 py-3 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl transition text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>


<script>
    const meterAwal = <?php echo e($previousReading ? (float)$previousReading->reading_value : 0); ?>;
    const readingInput = document.getElementById('reading_value');
    const pemakaianText = document.getElementById('pemakaian_text');

    readingInput.addEventListener('input', function() {
        const val = parseFloat(this.value) || 0;
        const diff = Math.max(0, val - meterAwal);
        pemakaianText.innerHTML = diff.toFixed(2) + ' <span class="text-sm font-normal text-gray-500">m³</span>';
    });

    const photoInput = document.getElementById('photoInput');
    const uploadDefault = document.getElementById('uploadDefault');
    const uploadPreview = document.getElementById('uploadPreview');
    const fileName = document.getElementById('fileName');
    const removePhoto = document.getElementById('removePhoto');

    photoInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            fileName.textContent = this.files[0].name;
            uploadDefault.classList.add('hidden');
            uploadPreview.classList.remove('hidden');
        }
    });

    removePhoto.addEventListener('click', function() {
        photoInput.value = '';
        uploadPreview.classList.add('hidden');
        uploadDefault.classList.remove('hidden');
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\WIN 11\Documents\SatuAir\SatuAir-Project\resources\views/petugas/meters/create.blade.php ENDPATH**/ ?>