<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Total Pemakaian</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="min-h-screen bg-[#f1f6f9] p-8">
    <main class="mx-auto max-w-5xl">
        <?php if (isset($component)) { $__componentOriginal92fc112d02076884fc15c4bd6eeeb47f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92fc112d02076884fc15c4bd6eeeb47f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.total-pemakaian','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('total-pemakaian'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92fc112d02076884fc15c4bd6eeeb47f)): ?>
<?php $attributes = $__attributesOriginal92fc112d02076884fc15c4bd6eeeb47f; ?>
<?php unset($__attributesOriginal92fc112d02076884fc15c4bd6eeeb47f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92fc112d02076884fc15c4bd6eeeb47f)): ?>
<?php $component = $__componentOriginal92fc112d02076884fc15c4bd6eeeb47f; ?>
<?php unset($__componentOriginal92fc112d02076884fc15c4bd6eeeb47f); ?>
<?php endif; ?>
    </main>
</body>
</html><?php /**PATH D:\KULIAH BRAY\SEMESTER 5\PPL\SatuAir-Project\resources\views/preview-total-pemakaian.blade.php ENDPATH**/ ?>