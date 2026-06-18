@extends('admin.layouts.app')
@section('page-title', __('global.admin_bulk_send'))
@section('content')
<div class="max-w-2xl">
    <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
        {{-- Stats summary --}}
        <div class="grid grid-cols-4 gap-3 mb-6">
            <div class="bg-indigo-50 dark:bg-indigo-950/30 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">{{ $totalCustomers }}</p>
                <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">{{ __('global.admin_total') }}</p>
            </div>
            <div class="bg-blue-50 dark:bg-blue-950/30 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $withEmail }}</p>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">{{ __('global.admin_online') }}</p>
            </div>
            <div class="bg-amber-50 dark:bg-amber-950/30 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ $withoutEmail }}</p>
                <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">{{ __('global.admin_offline_customer') }}</p>
            </div>
            <div class="bg-green-50 dark:bg-green-950/30 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $previousBuyers }}</p>
                <p class="text-xs text-green-600 dark:text-green-400 mt-1">{{ __('global.admin_previous_purchasers') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.whatsapp.bulk.send') }}">
            @csrf

            {{-- Audience --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">{{ __('global.admin_target_audience') }}</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-50 dark:hover:bg-indigo-950">
                        <input type="radio" name="audience" value="all" checked class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-medium">{{ __('global.admin_all_audience') }} ({{ $totalCustomers }})</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg border border-blue-200 dark:border-blue-800 hover:bg-blue-50 dark:hover:bg-blue-950">
                        <input type="radio" name="audience" value="online" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium">{{ __('global.admin_online_only') }} ({{ $withEmail }})</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg border border-amber-200 dark:border-amber-800 hover:bg-amber-50 dark:hover:bg-amber-950">
                        <input type="radio" name="audience" value="offline" class="text-amber-600 focus:ring-amber-500">
                        <span class="text-sm font-medium">{{ __('global.admin_offline_only') }} ({{ $withPhone - $withEmail }})</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg border border-green-200 dark:border-green-800 hover:bg-green-50 dark:hover:bg-green-950">
                        <input type="radio" name="audience" value="previous_buyers" class="text-green-600 focus:ring-green-500">
                        <span class="text-sm font-medium">{{ __('global.admin_previous_purchases_only') }} ({{ $previousBuyers }})</span>
                    </label>
                </div>
            </div>

            {{-- Channel --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">{{ __('global.admin_channel') }}</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                        <input type="radio" name="channel" value="whatsapp" checked class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-medium">{{ __('global.admin_whatsapp') }}</span>
                        <span class="text-xs text-gray-400">{{ __('global.admin_individual_links_desc') }}</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                        <input type="radio" name="channel" value="email" class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-medium">{{ __('global.admin_email') }}</span>
                        <span class="text-xs text-gray-400">{{ __('global.admin_automatic_free') }}</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800">
                        <input type="radio" name="channel" value="mixed" class="text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <span class="text-sm font-bold text-indigo-700 dark:text-indigo-300">{{ __('global.admin_mixed_recommended') }}</span>
                            <span class="text-xs text-gray-400 block">{{ __('global.admin_mixed_desc') }}</span>
                        </div>
                    </label>
                </div>
                <div id="channelWarning" class="mt-2 text-xs text-amber-600 hidden">
                    <span id="channelWarningText"></span>
                </div>
            </div>

            <div class="mb-4" id="subjectField" style="display:none">
                <label class="block text-sm font-medium mb-1">{{ __('global.admin_email_subject') }}</label>
                <input type="text" name="subject" value="{{ old('subject') }}"
                       class="w-full border rounded px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600"
                       placeholder="{{ __('global.admin_subject_placeholder') }}">
                @error('subject')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Message --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">{{ __('global.admin_message_text') }}</label>
                <textarea name="message" rows="8" required
                          class="w-full border rounded px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 @error('message') border-red-500 @enderror"
                          placeholder="{{ __('global.admin_write_message_here') }}">{{ old('message') }}</textarea>
                @error('message')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 transition text-sm">
                {{ __('global.admin_send') }}
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('input[name="channel"], input[name="audience"]').forEach(el => {
    el.addEventListener('change', showWarnings);
});
function showWarnings() {
    const channel = document.querySelector('input[name="channel"]:checked')?.value;
    const audience = document.querySelector('input[name="audience"]:checked')?.value;
    const warn = document.getElementById('channelWarning');
    const text = document.getElementById('channelWarningText');

    // Show subject field for email/mixed
    document.getElementById('subjectField').style.display = (channel === 'email' || channel === 'mixed') ? 'block' : 'none';

    // Warnings
    let msg = '';
    if (channel === 'email' && audience === 'offline') {
        msg = '{{ __('global.admin_warning_no_email_offline') }}';
    } else if (channel === 'email' && audience === 'all') {
        msg = '{{ __('global.admin_note_mixed_recommended') }}';
    }
    warn.className = msg ? 'mt-2 text-xs text-amber-600' : 'mt-2 text-xs text-amber-600 hidden';
    text.textContent = msg;
}
showWarnings();
</script>
@endpush
@endsection