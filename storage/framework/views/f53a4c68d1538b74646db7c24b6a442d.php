<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'meterAwal' => '317,00',
    'meterAkhir' => '342,00',
    'total' => '25,00',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'meterAwal' => '317,00',
    'meterAkhir' => '342,00',
    'total' => '25,00',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->class(['rounded-2xl bg-[#ebf3fe] p-6'])); ?>>
    <dl>
        <dt class="text-base font-semibold uppercase text-[#1873c7]">Total Pemakaian</dt>
        <dd class="mt-7 text-[2.5rem] leading-tight font-bold text-[#1873c7]">
            <?php echo e($total); ?> m³
        </dd>
    </dl>
    <p class="mt-6 text-lg text-[#57677a]">
        <?php echo e($meterAkhir); ?> − <?php echo e($meterAwal); ?> = <?php echo e($total); ?> m³
    </p>
</div>
<?php /**PATH D:\KULIAH BRAY\SEMESTER 5\PPL\SatuAir-Project\resources\views/components/total-pemakaian.blade.php ENDPATH**/ ?>