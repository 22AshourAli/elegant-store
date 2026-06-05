<?php $__env->startSection('page-title', $type === 'customers' ? __('global.admin_edit_customer') : __('global.admin_edit_user')); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <form action="<?php echo e(route('admin.users.update', $user)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('global.admin_name')); ?> <span class="text-red-500">*</span></label>
                <input type="text" name="name" required value="<?php echo e(old('name', $user->name)); ?>"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm">
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('global.email')); ?> <span class="text-red-500">*</span></label>
                <input type="email" name="email" required value="<?php echo e(old('email', $user->email)); ?>"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm">
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('global.admin_phone')); ?></label>
                <input type="text" name="phone" value="<?php echo e(old('phone', $user->phone)); ?>"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm">
                <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <?php if($type !== 'customers'): ?>
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('global.admin_role')); ?> <span class="text-red-500">*</span></label>
                <select name="role" id="role" required
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm">
                    <option value="manager" <?php echo e(old('role', $user->role) === 'manager' ? 'selected' : ''); ?>><?php echo e(__('global.admin_manager')); ?></option>
                    <option value="super_admin" <?php echo e(old('role', $user->role) === 'super_admin' ? 'selected' : ''); ?>><?php echo e(__('global.admin_super_admin')); ?></option>
                </select>
                <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="mb-6" id="branchField" <?php echo e($user->isManager() ? '' : 'style=display:none'); ?>>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('global.admin_branch')); ?></label>
                <select name="branch_id"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm">
                    <option value=""><?php echo e(__('global.admin_no_branch')); ?></option>
                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($branch->id); ?>" <?php echo e(old('branch_id', $user->branch_id) == $branch->id ? 'selected' : ''); ?>><?php echo e($branch->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5"><?php echo e(__('global.admin_branch_hint')); ?></p>
                <?php $__errorArgs = ['branch_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <?php endif; ?>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('global.password')); ?></label>
                <input type="password" name="password"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm"
                    placeholder="<?php echo e(__('global.admin_password_hint')); ?>">
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5"><?php echo e(__('global.admin_password_hint')); ?></p>
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">
                    <?php echo e(__('global.admin_update')); ?>

                </button>
                <a href="<?php echo e(route('admin.users.index', ['type' => $type])); ?>" class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <?php echo e(__('global.admin_cancel')); ?>

                </a>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<?php if($type !== 'customers'): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const role = document.getElementById('role');
        const branchField = document.getElementById('branchField');
        function toggleBranch() {
            branchField.style.display = role.value === 'manager' ? 'block' : 'none';
        }
        role.addEventListener('change', toggleBranch);
    });
</script>
<?php endif; ?>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\GH\Desktop\Projects\elegant-store\resources\views/admin/users/edit.blade.php ENDPATH**/ ?>