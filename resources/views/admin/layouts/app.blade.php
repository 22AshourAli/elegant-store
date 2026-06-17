<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
      x-data="{ darkMode: localStorage.getItem('darkMode') !== 'false', sidebarOpen: false }"
      x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); document.documentElement.classList.toggle('dark', val) })"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <script>
        (function() {
            if (localStorage.getItem('darkMode') !== 'false') document.documentElement.classList.add('dark');
        })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4f46e5">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    <title>@yield('title', __('global.admin_dashboard')) | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-200 transition-colors duration-300 min-h-screen" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

    <!-- Sidebar Overlay for mobile -->
    <div x-show="sidebarOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="lg:hidden fixed inset-0 z-30 bg-gray-950/60 backdrop-blur-sm"
         style="display: none;">
    </div>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
               class="fixed right-0 top-0 bottom-0 lg:static z-40 w-64 bg-white dark:bg-gray-900 border-l border-gray-100 dark:border-gray-800 overflow-y-auto transition-transform duration-300 ease-in-out shadow-lg lg:shadow-none flex flex-col">

            <!-- Sidebar Header with Logo and Close Button -->
            <div class="p-5 flex justify-between items-center border-b border-gray-150 dark:border-gray-800">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 hover:opacity-90 transition">
                    <svg class="h-8 w-8" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 2L30 16L16 30L2 16L16 2Z" class="fill-indigo-600 dark:fill-indigo-400"/>
                        <path d="M16 8L24 16L16 24L8 16L16 8Z" class="fill-white dark:fill-gray-900"/>
                        <path d="M16 13L19 16L16 19L13 16L16 13Z" class="fill-indigo-600 dark:fill-indigo-400"/>
                    </svg>
                    <div class="flex flex-col items-start">
                        <span class="text-lg font-extrabold tracking-tight text-gray-900 dark:text-white leading-none">ELEGANT</span>
                        <span class="text-[9px] font-semibold tracking-[0.3em] text-indigo-600 dark:text-indigo-400 leading-none">STORE</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Sidebar Navigation Links -->
            <div class="p-4 flex-1 overflow-y-auto">
                <nav class="space-y-1.5">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        {{ __('global.admin_dashboard') }}
                    </a>
                    <a href="{{ route('admin.branches.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ request()->routeIs('admin.branches.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        {{ __('global.admin_branches') }}
                    </a>
                    @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                        {{ __('global.admin_users') }}
                    </a>
                    @endif
                    <a href="{{ route('admin.customers.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ request()->routeIs('admin.customers.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        {{ __('global.admin_customers') }}
                    </a>
                    <a href="{{ route('admin.pos.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ request()->routeIs('admin.pos.*') && !request()->routeIs('admin.pos.return*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                        </svg>
                        {{ __('global.pos_title') }}
                    </a>
                    <a href="{{ route('admin.pos.return') }}" class="flex items-center gap-3 px-4 py-2 rounded-xl text-sm font-medium transition duration-200 mr-6 {{ request()->routeIs('admin.pos.return*') ? 'bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 font-semibold' : 'text-gray-500 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        {{ __('global.pos_return') }}
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ request()->routeIs('admin.categories.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                        {{ __('global.admin_categories') }}
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ request()->routeIs('admin.products.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        {{ __('global.admin_products') }}
                    </a>
                    <a href="{{ route('admin.coupons.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ request()->routeIs('admin.coupons.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2zM9 16H9m3 0H12m3 0H15" />
                        </svg>
                        {{ __('global.admin_coupons') }}
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ request()->routeIs('admin.orders.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        {{ __('global.admin_orders') }}
                    </a>
                    <a href="{{ route('admin.returns.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ request()->routeIs('admin.returns.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        {{ __('global.return_requests') }}
                    </a>
                    <a href="{{ route('admin.exchanges.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ request()->routeIs('admin.exchanges.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        {{ __('global.admin_exchanges') }}
                    </a>
                    <a href="{{ route('admin.expenses.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ request()->routeIs('admin.expenses.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ __('global.admin_expenses') }}
                    </a>
                    <a href="{{ route('admin.suppliers.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ request()->routeIs('admin.suppliers.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        {{ __('global.admin_suppliers') }}
                    </a>
                    <div class="pt-3 mt-3 border-t border-slate-200 dark:border-slate-700">
                        <p class="px-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">{{ __('global.admin_shipping_settings') }}</p>
                        <a href="{{ route('admin.governorates.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ request()->routeIs('admin.governorates.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h1.5a3 3 0 003-3V6.7m-2 9l-3-3m0 0l-3 3m3-3v12M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                            </svg>
                            {{ __('global.admin_governorates') }}
                        </a>
                        <a href="{{ route('admin.cities.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 mr-6 {{ request()->routeIs('admin.cities.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-500 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            {{ __('global.admin_cities') }}
                        </a>
                        <a href="{{ route('admin.shipping-settings.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 mr-6 {{ request()->routeIs('admin.shipping-settings.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-500 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                             </svg>
                             {{ __('global.admin_shipping_settings') }}
                         </a>
                         <a href="{{ route('admin.carriers.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 mr-6 {{ request()->routeIs('admin.carriers.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-500 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                             </svg>
                             {{ __('global.admin_carriers') }}
                         </a>
                         <a href="{{ route('admin.shipping-rates.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 mr-6 {{ request()->routeIs('admin.shipping-rates.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-500 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                             </svg>
                             {{ __('global.admin_shipping_rates') }}
                         </a>
                    </div>
                    <a href="{{ route('admin.purchase-orders.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ request()->routeIs('admin.purchase-orders.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        {{ __('global.admin_purchase_orders') }}
                    </a>
                    <a href="{{ route('admin.stock-transfers.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ request()->routeIs('admin.stock-transfers.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        {{ __('global.admin_stock_transfers') }}
                    </a>
                    <a href="{{ route('admin.whatsapp.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ request()->routeIs('admin.whatsapp.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        {{ __('global.admin_whatsapp') }}
                    </a>
                    <div class="pt-3 mt-3 border-t border-slate-200 dark:border-slate-700">
                        <p class="px-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">التقارير</p>
                        <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 {{ request()->routeIs('admin.reports.*') ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            التقارير والإحصائيات
                        </a>
                    </div>
                </nav>
            </div>

            <!-- Sidebar Footer -->
            <div class="mt-auto p-4 border-t border-gray-100 dark:border-gray-800">
                <div class="space-y-1.5">
                    <!-- Dark Mode -->
                    <button @click="darkMode = !darkMode"
                            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white">
                        <svg x-show="!darkMode" class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        <svg x-show="darkMode" class="w-5 h-5 text-amber-500" style="display: none;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><path stroke-linecap="round" stroke-linejoin="round" d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"></path></svg>
                        <span x-show="!darkMode">داكن</span>
                        <span x-show="darkMode" style="display: none;">فاتح</span>
                    </button>

                    <!-- Language Switcher -->
                    <div x-data="{ langOpen: false }" class="relative">
                        <button @click="langOpen = !langOpen"
                                class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h1.5a3 3 0 003-3V6.7m-2 9l-3-3m0 0l-3 3m3-3v12M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                            <span>{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
                            <svg class="w-3 h-3 mr-auto transition-transform" :class="langOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="langOpen" @click.away="langOpen = false" style="display: none;"
                             class="mr-10 mt-1 rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 py-1">
                            <a href="{{ route('lang.switch', 'ar') }}" class="flex items-center justify-between px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ app()->getLocale() === 'ar' ? 'text-indigo-600 dark:text-indigo-400 font-extrabold' : '' }}">
                                <span>العربية</span>
                                @if(app()->getLocale() === 'ar') <svg class="w-3 h-3 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> @endif
                            </a>
                            <a href="{{ route('lang.switch', 'en') }}" class="flex items-center justify-between px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ app()->getLocale() === 'en' ? 'text-indigo-600 dark:text-indigo-400 font-extrabold' : '' }}">
                                <span>English</span>
                                @if(app()->getLocale() === 'en') <svg class="w-3 h-3 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> @endif
                            </a>
                        </div>
                    </div>

                    <!-- Back to Store -->
                    <a href="{{ route('home') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition duration-200 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        <span>{{ __('global.admin_back_to_store') }}</span>
                    </a>
                </div>

                <!-- User Info & Logout -->
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 truncate flex-1 min-w-0">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('admin.logout') }}" class="m-0 flex-shrink-0">
                        @csrf
                        <button class="p-2 rounded-xl text-sm font-semibold transition bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            <span class="hidden lg:inline">خروج</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top bar -->
            <header class="bg-white dark:bg-gray-900 shadow-sm px-3 sm:px-4 py-3 flex justify-between items-center border-b border-gray-100 dark:border-gray-800 transition overflow-visible">
                <div class="flex items-center gap-2 sm:gap-4 min-w-0">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <h1 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white truncate">@yield('page-title')</h1>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-3 flex-shrink-0">
                    <!-- Notifications -->
                    <div x-data="notifications()" x-init="init()">
                        <div class="relative cursor-pointer">
                            <button @click="togglePanel" class="relative p-1.5 sm:p-2 cursor-pointer rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-500 hover:scale-105 transition duration-150">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0m6 0H9"></path>
                                </svg>
                                <span x-show="unread > 0" x-text="unread" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1" style="display:none"></span>
                            </button>

                            <!-- Dropdown Panel -->
                            <div x-show="open" @click.outside="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="fixed sm:absolute cursor-pointer z-50 top-14 sm:top-auto inset-x-2 sm:inset-x-auto sm:mt-2 {{ app()->getLocale() === 'ar' ? 'sm:left-0' : 'sm:right-0' }} w-auto sm:w-80 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 max-h-96 overflow-hidden"
                                 style="display:none">
                                <div class="p-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ __('global.admin_notifications') }}</span>
                                    <button x-show="unread > 0" @click="markAllRead" class="text-xs cursor-pointer text-indigo-600 dark:text-indigo-400 hover:underline flex-shrink-0">{{ __('global.admin_mark_all_read') }}</button>
                                </div>
                                <div class="overflow-y-auto max-h-72">
                                    <div x-show="items.length === 0" class="p-6 text-center text-gray-400 text-sm" style="display:none">{{ __('global.admin_no_notifications') }}</div>
                                    <template x-for="n in items" :key="n.id">
                                        <a :href="n.url || '#'" @click="n.read_at ? null : markRead(n.id)" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition border-b border-gray-50 dark:border-gray-700/50" :class="{'bg-indigo-50/50 dark:bg-indigo-950/20': !n.read_at}">
                                            <div class="flex items-start gap-3">
                                                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center" :class="n.type === 'exchange' ? 'bg-green-100 dark:bg-green-900/30 text-green-600' : n.type === 'return' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-600' : n.type === 'order' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600' : 'bg-gray-100 dark:bg-gray-700 text-gray-500'">
                                                    <template x-if="n.type === 'exchange'">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                                    </template>
                                                    <template x-if="n.type === 'return'">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3"/></svg>
                                                    </template>
                                                    <template x-if="n.type === 'order'">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                    </template>
                                                    <template x-if="n.type === 'info'">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
                                                    </template>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate" x-text="n.title"></p>
                                                    <p class="text-xs text-gray-500 mt-0.5" x-text="n.time"></p>
                                                </div>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                                <a href="{{ route('admin.notifications.index') }}" class="block cursor-pointer p-3 text-center text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition border-t border-gray-100 dark:border-gray-700">
                                    {{ __('global.admin_notifications_all') }}
                                </a>
                            </div>
                        </div>

                        <!-- Toast Notification -->
                        <div x-show="toastItem" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-8"
                             class="fixed top-4 {{ app()->getLocale() === 'ar' ? 'left-4' : 'right-4' }} z-[100] max-w-sm w-full cursor-pointer" style="display:none">
                            <a :href="toastItem.url || '#'" @click="dismissToast(); toastItem.read_at ? null : markRead(toastItem.id)"
                               class="flex items-start gap-3 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/60 dark:border-gray-700/60 p-4 hover:shadow-xl transition-shadow">
                                <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center shadow-sm"
                                     :class="toastItem.type === 'exchange' ? 'bg-emerald-100 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400' : toastItem.type === 'return' ? 'bg-amber-100 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400' : toastItem.type === 'order' ? 'bg-indigo-100 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-500'">
                                    <template x-if="toastItem.type === 'exchange'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                    </template>
                                    <template x-if="toastItem.type === 'return'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3"/></svg>
                                    </template>
                                    <template x-if="toastItem.type === 'order'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </template>
                                    <template x-if="toastItem.type === 'info'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
                                    </template>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-extrabold text-indigo-600 dark:text-indigo-400 tracking-wide">@lang('global.notification_new')</p>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white truncate mt-0.5" x-text="toastItem.title"></p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span x-text="toastItem.time"></span>
                                    </p>
                                </div>
                                <button @click.prevent="dismissToast()" class="flex-shrink-0 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors focus-visible:outline-none" aria-label="Dismiss">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </a>
                        </div>
                    </div>

                </div>
            </header>

            <main class="p-3 sm:p-4 md:p-6">
                @yield('content')
            </main>

            <div x-data="{ toasts: [] }" @toast.window="toasts.push($event.detail); setTimeout(() => toasts.shift(), 4500)" class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none">
                <template x-for="(toast, index) in toasts" :key="index">
                    <div class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl border text-sm font-bold transition-all duration-300 max-w-sm"
                         :class="toast.type === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/90 border-emerald-200 dark:border-emerald-700 text-emerald-800 dark:text-emerald-200' : 'bg-red-50 dark:bg-red-900/90 border-red-200 dark:border-red-700 text-red-800 dark:text-red-200'"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 scale-95">
                        <template x-if="toast.type === 'success'">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </template>
                        <template x-if="toast.type !== 'success'">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </template>
                        <span x-text="toast.message" class="flex-1"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        function playNotifSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain); gain.connect(ctx.destination);
                osc.frequency.setValueAtTime(800, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(600, ctx.currentTime + 0.15);
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.25);
                osc.start(ctx.currentTime); osc.stop(ctx.currentTime + 0.25);
            } catch(e) {}
        }
        document.addEventListener('alpine:init', () => {
            Alpine.data('notifications', () => ({
                open: false,
                unread: 0,
                items: [],
                latestId: null,
                _pollTimer: null,
                _retrySec: 3,
                _bc: null,
                _baseTitle: '',
                toastItem: null,
                toastTimer: null,
                _online: true,

                async init() {
                    this._baseTitle = document.title.replace(/^\(\d+\) /, '');
                    await this.syncAll();
                    this.scheduleNext();
                    try {
                        this._bc = new BroadcastChannel('notifications');
                        this._bc.onmessage = (e) => {
                            if (e.data.type === 'new_notification') {
                                this.unread = e.data.count;
                                this.latestId = e.data.latestId;
                                this.fetchItems();
                                this._updateTitle();
                            }
                        };
                    } catch(e) {}
                    window.addEventListener('online', () => { this._online = true; this.syncAll(); this.scheduleNext(); });
                    window.addEventListener('offline', () => { this._online = false; });
                },

                async syncAll() {
                    await this.fetchUnread();
                    await this.fetchItems();
                },

                scheduleNext() {
                    if (this._pollTimer) clearTimeout(this._pollTimer);
                    const poll = async () => {
                        const ok = await this.fetchUnread();
                        if (ok) {
                            this._retrySec = 3;
                        } else {
                            this._retrySec = Math.min(this._retrySec * 2, 30);
                        }
                        if (this.unread > 0) this.fetchItems();
                        this.scheduleNext();
                    };
                    this._pollTimer = setTimeout(poll, this._retrySec * 1000);
                },

                async fetchUnread() {
                    try {
                        const res = await fetch("{{ route('admin.notifications.unread-count') }}");
                        if (!res.ok) return false;
                        const data = await res.json();
                        const isNew = this.latestId && data.latest_id && data.latest_id !== this.latestId && data.count > 0;
                        if (isNew) {
                            playNotifSound();
                            this.fetchItems().then(() => {
                                const first = this.items.find(i => i.id === data.latest_id);
                                if (first && !first.read_at) this.showToast(first);
                            });
                            try { this._bc?.postMessage({ type: 'new_notification', count: data.count, latestId: data.latest_id }); } catch(e) {}
                        }
                        this.latestId = data.latest_id || this.latestId;
                        this.unread = data.count;
                        this._updateTitle();
                        return true;
                    } catch(e) { return false; }
                },

                async fetchItems() {
                    try {
                        const res = await fetch("{{ route('admin.notifications.index') }}?json=1");
                        const data = await res.json();
                        this.items = data.notifications || [];
                    } catch(e) {}
                },

                togglePanel() {
                    this.open = !this.open;
                    if (this.open) { this.fetchItems(); this.dismissToast(); }
                },

                showToast(n) {
                    this.toastItem = n;
                    if (this.toastTimer) clearTimeout(this.toastTimer);
                    this.toastTimer = setTimeout(() => { this.toastItem = null; }, 5000);
                },

                dismissToast() {
                    this.toastItem = null;
                    if (this.toastTimer) { clearTimeout(this.toastTimer); this.toastTimer = null; }
                },

                async markRead(id) {
                    try {
                        const res = await fetch("{{ url('admin/notifications') }}/" + id + "/read", { method: "POST", headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" } });
                        if (!res.ok) return;
                        this.unread = Math.max(0, this.unread - 1);
                        const n = this.items.find(i => i.id === id);
                        if (n) n.read_at = true;
                        this._updateTitle();
                    } catch(e) {}
                },

                async markAllRead() {
                    try {
                        const res = await fetch("{{ route('admin.notifications.mark-all-read') }}", { method: "POST", headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" } });
                        if (!res.ok) return;
                        this.unread = 0;
                        this.items.forEach(n => n.read_at = true);
                        this._updateTitle();
                    } catch(e) {}
                },

                _updateTitle() {
                    document.title = this.unread > 0 ? `(${this.unread}) ${this._baseTitle}` : this._baseTitle;
                },

                destroy() {
                    if (this._pollTimer) clearTimeout(this._pollTimer);
                    if (this.toastTimer) clearTimeout(this.toastTimer);
                    try { this._bc?.close(); } catch(e) {}
                }
            }));
        });
    </script>
    @endpush
    @stack('scripts')
</body>
</html>
