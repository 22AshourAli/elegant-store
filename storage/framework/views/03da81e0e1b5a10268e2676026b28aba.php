<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-20 text-center">
    <h1 class="text-8xl font-extrabold text-indigo-600 mb-4">404</h1>
    <p class="text-2xl font-bold text-gray-900 dark:text-white mb-4"><?php echo e(__('global.page_not_found')); ?></p>
    <p class="text-gray-500 mb-8"><?php echo e(__('global.page_not_found_desc')); ?></p>
    <a href="<?php echo e(route('home')); ?>" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg transition-colors"><?php echo e(__('global.home')); ?></a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.store', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\GH\Desktop\Projects\elegant-store\resources\views/errors/404.blade.php ENDPATH**/ ?>