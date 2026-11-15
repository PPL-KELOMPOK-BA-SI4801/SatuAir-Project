<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'size' => 'md',       // sm, md, lg
    'variant' => 'dark',   // dark (for light bg), light (for dark bg)
    'withText' => true,
    'subtitle' => null,
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
    'size' => 'md',       // sm, md, lg
    'variant' => 'dark',   // dark (for light bg), light (for dark bg)
    'withText' => true,
    'subtitle' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $sizes = [
        'sm' => ['box' => 'w-8 h-8', 'icon' => 'w-4 h-4', 'title' => 'text-lg', 'sub' => 'text-[10px]'],
        'md' => ['box' => 'w-10 h-10', 'icon' => 'w-5 h-5', 'title' => 'text-xl', 'sub' => 'text-xs'],
        'lg' => ['box' => 'w-12 h-12', 'icon' => 'w-6 h-6', 'title' => 'text-2xl', 'sub' => 'text-xs'],
    ][$size] ?? ['box' => 'w-10 h-10', 'icon' => 'w-5 h-5', 'title' => 'text-xl', 'sub' => 'text-xs'];

    $isLight = $variant === 'light';
?>

<div <?php echo e($attributes->merge(['class' => 'inline-flex items-center gap-3 select-none group'])); ?>>
    
    <div class="<?php echo e($sizes['box']); ?> rounded-xl flex items-center justify-center transition-all duration-300 <?php echo e($isLight ? 'bg-gradient-to-br from-sky-400 to-blue-600 shadow-md shadow-sky-950/20 text-white' : 'bg-gradient-to-br from-[#0F2744] via-[#123863] to-[#0284C7] text-white shadow-md shadow-sky-900/10'); ?>">
        <svg class="<?php echo e($sizes['icon']); ?> fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z" />
            <path d="M12 6.5l3.18 3.18a4.5 4.5 0 0 1-6.36 0z" fill="white" fill-opacity="0.35" />
            <circle cx="9.5" cy="14" r="1.5" fill="white" fill-opacity="0.45" />
        </svg>
    </div>

    <?php if($withText): ?>
        <div class="leading-tight">
            <div class="flex items-center gap-1.5 font-bold tracking-tight <?php echo e($sizes['title']); ?> <?php echo e($isLight ? 'text-white' : 'text-slate-900'); ?>">
                <span>Satu<span class="text-sky-500">Air</span></span>
            </div>
            <?php if($subtitle): ?>
                <p class="<?php echo e($sizes['sub']); ?> font-medium <?php echo e($isLight ? 'text-sky-200/70' : 'text-slate-400'); ?> tracking-wide"><?php echo e($subtitle); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php /**PATH C:\Users\WIN 11\Documents\SatuAir\SatuAir-Project\resources\views/components/logo.blade.php ENDPATH**/ ?>