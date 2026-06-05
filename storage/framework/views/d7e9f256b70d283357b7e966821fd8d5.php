<?php if($products->count() > 0): ?>
    <div class="product-grid stagger in">
        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="product-card-wrapper">
                <?php echo $__env->make('shop.partials.product-card', ['product' => $product, 'wishlistIds' => $wishlistIds ?? [], 'cartProductIds' => $cartProductIds ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="mt-8 flex items-center justify-center">
        <?php echo e($products->onEachSide(1)->links('vendor.pagination.tailwind')); ?>

    </div>
<?php else: ?>
    <div class="text-center text-slate-500 py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800">
        <?php echo e(__('global.filter_no_results')); ?>

    </div>
<?php endif; ?>
<?php /**PATH C:\Users\GH\Desktop\Projects\elegant-store\resources\views/shop/partials/product-grid.blade.php ENDPATH**/ ?>