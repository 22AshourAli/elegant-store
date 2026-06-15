@extends('admin.layouts.app')
@section('page-title', __('global.admin_manage_users'))
@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('global.admin_users') }}</h2>
    <a href="{{ route('admin.users.create', ['type' => $type]) }}" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        {{ $type === 'customers' ? __('global.admin_add_customer') : __('global.admin_add_user') }}
    </a>
</div>

@if(session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 p-3 rounded-lg mb-4 text-sm font-medium">{{ session('success') }}</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-4">
    <div class="flex border-b border-gray-100 dark:border-gray-700">
        <a href="{{ route('admin.users.index', ['type' => 'staff']) }}"
           class="flex-1 sm:flex-none px-5 py-3 text-sm font-semibold text-center transition border-b-2 {{ $type === 'staff' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            {{ __('global.admin_staff') }}
            <span class="ml-1.5 text-xs {{ $type === 'staff' ? 'text-indigo-400' : 'text-gray-400' }}">({{ $counts['staff'] }})</span>
        </a>
        <a href="{{ route('admin.users.index', ['type' => 'customers']) }}"
           class="flex-1 sm:flex-none px-5 py-3 text-sm font-semibold text-center transition border-b-2 {{ $type === 'customers' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            {{ __('global.admin_customers') }}
            <span class="ml-1.5 text-xs {{ $type === 'customers' ? 'text-indigo-400' : 'text-gray-400' }}">({{ $counts['customers'] }})</span>
        </a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                    <th class="p-3 text-right font-semibold text-gray-600 dark:text-gray-400">{{ __('global.admin_name') }}</th>
                    <th class="p-3 text-right font-semibold text-gray-600 dark:text-gray-400 hidden md:table-cell">{{ __('global.email') }}</th>
                    <th class="p-3 text-right font-semibold text-gray-600 dark:text-gray-400">{{ __('global.admin_role') }}</th>
                    <th class="p-3 text-right font-semibold text-gray-600 dark:text-gray-400 hidden md:table-cell">{{ __('global.admin_phone') }}</th>
                    <th class="p-3 text-right font-semibold text-gray-600 dark:text-gray-400 hidden md:table-cell">{{ __('global.admin_date') }}</th>
                    @if($type === 'staff')
                    <th class="p-3 text-center font-semibold text-gray-600 dark:text-gray-400 hidden md:table-cell">{{ __('global.admin_branch') }}</th>
                    @endif
                    <th class="p-3 text-center font-semibold text-gray-600 dark:text-gray-400">{{ __('global.admin_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/20 transition even:bg-gray-50/50 dark:even:bg-gray-700/20">
                    <td class="p-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full {{ $user->isCustomer() ? 'bg-gray-100 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400' : 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400' }} flex items-center justify-center font-bold text-sm shrink-0">
                                {{ mb_substr($user->name, 0, 2) }}
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white">{{ $user->name }}</div>
                                <div class="text-xs text-gray-400 dark:text-gray-500">{{ $user->phone ?? '-' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="p-3 text-gray-600 dark:text-gray-400 hidden md:table-cell">{{ $user->email }}</td>
                    <td class="p-3">
                        @if($user->isSuperAdmin())
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                {{ __('global.admin_super_admin') }}
                            </span>
                        @elseif($user->isManager())
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                {{ __('global.admin_manager') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-700/50 text-gray-600 dark:text-gray-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                {{ __('global.admin_customer') }}
                            </span>
                        @endif
                    </td>
                    <td class="p-3 text-gray-600 dark:text-gray-400 hidden md:table-cell">{{ $user->phone ?? '-' }}</td>
                    <td class="p-3 text-gray-500 dark:text-gray-400 text-xs hidden md:table-cell">{{ $user->created_at->format('Y-m-d') }}</td>
                    @if($type === 'staff')
                    <td class="p-3 text-center text-gray-600 dark:text-gray-400 hidden md:table-cell">
                        @if($user->branch)
                            <span class="inline-flex items-center gap-1 text-sm">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                {{ $user->branch->name }}
                            </span>
                        @else
                            <span class="text-gray-400 dark:text-gray-500 text-sm italic">{{ __('global.admin_no_branch') }}</span>
                        @endif
                    </td>
                    @endif
                    <td class="p-3 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                {{ __('global.admin_edit') }}
                            </a>
                            @if(!$user->isSuperAdmin())
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('{{ __("global.confirm_delete") }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    {{ __('global.admin_delete') }}
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $type === 'staff' ? 7 : 6 }}" class="p-10 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path></svg>
                        <p class="text-gray-400 dark:text-gray-500 text-sm">{{ __('global.admin_no_users') }}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(method_exists($users, 'links'))
<div class="mt-4">
    {{ $users->onEachSide(1)->links('vendor.pagination.admin') }}
</div>
@endif
@endsection
