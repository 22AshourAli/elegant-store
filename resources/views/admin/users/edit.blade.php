@extends('admin.layouts.app')
@section('page-title', $type === 'customers' ? __('global.admin_edit_customer') : __('global.admin_edit_user'))
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.admin_name') }} <span class="text-red-500">*</span></label>
                <input type="text" name="name" required value="{{ old('name', $user->name) }}"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm">
                @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.email') }} <span class="text-red-500">*</span></label>
                <input type="email" name="email" required value="{{ old('email', $user->email) }}"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm">
                @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.admin_phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm">
                @error('phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            @if($type !== 'customers')
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.admin_role') }} <span class="text-red-500">*</span></label>
                <select name="role" id="role" required
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm">
                    <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>{{ __('global.admin_manager') }}</option>
                    <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>{{ __('global.admin_super_admin') }}</option>
                </select>
                @error('role') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="mb-6" id="branchField" {{ $user->isManager() ? '' : 'style=display:none' }}>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.admin_branch') }}</label>
                <select name="branch_id"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm">
                    <option value="">{{ __('global.admin_no_branch') }}</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">{{ __('global.admin_branch_hint') }}</p>
                @error('branch_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
            @endif

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.password') }}</label>
                <input type="password" name="password"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm"
                    placeholder="{{ __('global.admin_password_hint') }}">
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">{{ __('global.admin_password_hint') }}</p>
                @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">
                    {{ __('global.admin_update') }}
                </button>
                <button type="button" onclick="openForceReset()"
                    class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    {{ __('global.admin_force_reset_password') }}
                </button>
                <a href="{{ route('admin.users.index', ['type' => $type]) }}" class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    {{ __('global.admin_cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Force Reset Password Modal -->
<div id="forceResetModal" class="fixed inset-0 z-50 items-center justify-center bg-black/50 backdrop-blur-sm hidden" style="display:none">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md mx-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">{{ __('global.admin_force_reset_password') }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('global.admin_force_reset_confirm') }}</p>
                <div id="forceResetResult" style="display:none">
                    <div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4 mb-4">
                        <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300 mb-2">{{ __('global.admin_new_password') }}</p>
                        <div class="flex items-center gap-2">
                            <code id="newPasswordDisplay" class="flex-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-base font-mono font-bold text-gray-900 dark:text-white select-all"></code>
                            <button onclick="copyPassword()" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition" title="{{ __('global.admin_copy') }}">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">{{ __('global.admin_save_password_hint') }}</p>
                    </div>
                </div>
                <div id="forceResetError" class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-4" style="display:none">
                    <p id="forceResetErrorMessage" class="text-sm font-medium text-red-700 dark:text-red-400"></p>
                </div>
                <div class="flex items-center gap-3">
                    <button id="forceResetConfirmBtn" onclick="doForceReset()" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">
                        {{ __('global.admin_reset') }}
                    </button>
                    <button id="forceResetCloseBtn" onclick="closeForceReset()" class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        {{ __('global.admin_close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    var currentUserId = {{ $user->id }};
    var resetPassword = '';
    var forceResetModal = document.getElementById('forceResetModal');

    function openForceReset() {
        document.getElementById('forceResetResult').style.display = 'none';
        document.getElementById('forceResetError').style.display = 'none';
        document.getElementById('forceResetConfirmBtn').style.display = '';
        forceResetModal.classList.remove('hidden');
        forceResetModal.style.display = 'flex';
        forceResetModal.offsetWidth;
    }

    function closeForceReset() {
        forceResetModal.classList.add('hidden');
        forceResetModal.style.display = 'none';
    }

    function doForceReset() {
        var btn = document.getElementById('forceResetConfirmBtn');
        var closeBtn = document.getElementById('forceResetCloseBtn');
        btn.disabled = true;
        btn.textContent = '{{ __('global.admin_processing') }}...';
        closeBtn.disabled = true;

        fetch('/admin/users/' + currentUserId + '/force-reset-password', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) {
                resetPassword = data.password;
                document.getElementById('newPasswordDisplay').textContent = data.password;
                document.getElementById('forceResetResult').style.display = 'block';
                document.getElementById('forceResetConfirmBtn').style.display = 'none';
                document.getElementById('forceResetCloseBtn').textContent = '{{ __('global.admin_close') }}';
            } else {
                document.getElementById('forceResetErrorMessage').textContent = data.message || '{{ __('global.admin_error') }}';
                document.getElementById('forceResetError').style.display = 'block';
            }
            btn.disabled = false;
            closeBtn.disabled = false;
            btn.textContent = '{{ __('global.admin_reset') }}';
        }).catch(function() {
            document.getElementById('forceResetErrorMessage').textContent = '{{ __('global.admin_network_error') }}';
            document.getElementById('forceResetError').style.display = 'block';
            btn.disabled = false;
            closeBtn.disabled = false;
            btn.textContent = '{{ __('global.admin_reset') }}';
        });
    }

    function copyPassword() {
        if (resetPassword) {
            navigator.clipboard.writeText(resetPassword).then(function() {
                var btn = document.querySelector('#forceResetResult button');
                btn.innerHTML = '<svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
                setTimeout(function() {
                    btn.innerHTML = '<svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>';
                }, 2000);
            });
        }
    }

    forceResetModal.addEventListener('click', function(e) {
        if (e.target === forceResetModal) closeForceReset();
    });
</script>
@endpush

@push('scripts')
@if($type !== 'customers')
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
@endif
@endpush
@endsection
