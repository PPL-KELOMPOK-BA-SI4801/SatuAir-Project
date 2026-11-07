<?php use \App\Services\HitungPemakaianMeter; ?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catat Pemakaian Air — SatuAir</title>
    <link rel="stylesheet" href="<?php echo e(asset('assets/preview-pbi-09.css')); ?>">
    <script src="<?php echo e(asset('assets/preview-pbi-09.js')); ?>" defer></script>
</head>
<body>
    <header class="topbar">
        <strong>SatuAir</strong>
        <span>Preview PBI-09</span>
    </header>

    <main>
        <h1>Catat Pemakaian Air</h1>
        <p class="subtitle">Masukkan meter akhir untuk menghitung total pemakaian air. Preview ini tidak menyimpan pencatatan.</p>

        <form class="record-form" id="meter-form" data-awal="<?php echo e($meterAwal); ?>" method="get" action="<?php echo e(route('meter.preview-pbi-09')); ?>">
            <div class="field-grid">
                <label for="meter-awal">
                    Meter Awal (m³)
                    <input id="meter-awal" readonly value="<?php echo e(HitungPemakaianMeter::format($meterAwal)); ?>">
                </label>
                <label for="meter-akhir">
                    Meter Akhir (m³)
                    <input id="meter-akhir" name="meter_akhir" type="text" inputmode="decimal" placeholder="Contoh 342,00"
                        value="<?php echo e($input); ?>" required maxlength="12" aria-describedby="meter-help meter-error"
                        <?php if($pesan !== null): ?> aria-invalid="true" <?php endif; ?>>
                    <small id="meter-help">Gunakan koma atau titik, maksimal 2 angka desimal.</small>
                    <span id="meter-error" class="field-error" role="status"><?php echo e($pesan); ?></span>
                </label>
            </div>

            <div class="calculation" aria-live="polite" aria-atomic="true">
                <div>
                    <span>Total Pemakaian</span>
                    <strong id="total-pemakaian"><?php echo e($pemakaian === null ? '—' : HitungPemakaianMeter::format($pemakaian)); ?> m³</strong>
                </div>
                <p id="rumus">
                    <?php if($pemakaian === null): ?>
                        Isi meter akhir yang valid untuk melihat perhitungan.
                    <?php else: ?>
                        <?php echo e(HitungPemakaianMeter::format($meterAkhir)); ?> − <?php echo e(HitungPemakaianMeter::format($meterAwal)); ?> = <?php echo e(HitungPemakaianMeter::format($pemakaian)); ?> m³
                    <?php endif; ?>
                </p>
            </div>

            <button type="submit">Cek di server</button>
        </form>
    </main>
</body>
</html>
<?php /**PATH D:\KULIAH BRAY\SEMESTER 5\PPL\SatuAir-Project\resources\views/meter/preview-pbi-09.blade.php ENDPATH**/ ?>