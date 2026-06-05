<?php if($paginator->hasPages()): ?>
    <nav role="navigation" aria-label="Pagination" class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4">
        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 order-2 sm:order-1">
            <?php if($paginator->firstItem()): ?>
                <?php echo e(__('global.showing')); ?>

                <span class="font-semibold text-gray-700 dark:text-gray-200"><?php echo e($paginator->firstItem()); ?></span>
                <?php echo e(__('global.to')); ?>

                <span class="font-semibold text-gray-700 dark:text-gray-200"><?php echo e($paginator->lastItem()); ?></span>
                <?php echo e(__('global.of')); ?>

                <span class="font-semibold text-gray-700 dark:text-gray-200"><?php echo e($paginator->total()); ?></span>
            <?php else: ?>
                <?php echo e($paginator->count()); ?>

            <?php endif; ?>
        </div>

        <div class="flex items-center gap-1.5 order-1 sm:order-2">
            <?php if($paginator->onFirstPage()): ?>
                <span class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg text-gray-300 dark:text-gray-600 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </span>
            <?php else: ?>
                <a href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            <?php endif; ?>

            <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(is_string($element)): ?>
                    <span class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 text-xs sm:text-sm text-gray-400 dark:text-gray-500">...</span>
                <?php endif; ?>

                <?php if(is_array($element)): ?>
                    <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page == $paginator->currentPage()): ?>
                            <span class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-indigo-600 text-white text-xs sm:text-sm font-bold shadow-sm"><?php echo e($page); ?></span>
                        <?php else: ?>
                            <a href="<?php echo e($url); ?>" class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg text-gray-600 dark:text-gray-300 text-xs sm:text-sm hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition" aria-label="<?php echo e(__('Go to page :page', ['page' => $page])); ?>"><?php echo e($page); ?></a>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($paginator->hasMorePages()): ?>
                <a href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            <?php else: ?>
                <span class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg text-gray-300 dark:text-gray-600 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            <?php endif; ?>
        </div>
    </nav>
<?php endif; ?>
<?php /**PATH C:\Users\GH\Desktop\Projects\elegant-store\resources\views/vendor/pagination/tailwind.blade.php ENDPATH**/ ?>