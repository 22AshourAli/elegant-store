<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ darkMode: localStorage.getItem('darkMode') !== 'false' }"
      x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); document.documentElement.classList.toggle('dark', val) })"
      :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <script>
            if (localStorage.getItem('darkMode') !== 'false') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Elegant Store') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" media="print" onload="this.media='all'" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-950 min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 transition-colors duration-300 px-4" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

        <!-- Premium Controls in Guest Layout (Lang + Theme Switchers) -->
        <div class="fixed top-4 right-4 z-50 flex items-center gap-2">
            <!-- Language Switcher -->
            <div x-data="{ langOpen: false }" class="relative">
                <button @click="langOpen = !langOpen" class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white dark:bg-gray-800 shadow-md border border-gray-100 dark:border-gray-700 hover:scale-105 transition duration-200 text-xs font-bold text-gray-700 dark:text-gray-300">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 11.37 7.31 16.5 3 19M6.412 9a15.008 15.008 0 002.5-3m0 0h5M8.912 6a17.979 17.979 0 011.082 3.9M16 11l-5 10" /></svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
                </button>
                <div x-show="langOpen" @click.away="langOpen = false" x-transition class="absolute right-0 mt-2 w-32 bg-white dark:bg-gray-850 rounded-xl shadow-xl border border-gray-100 dark:border-gray-750 py-1.5 z-50 text-start text-xs font-semibold" style="display: none;">
                    <a href="{{ route('lang.switch', 'ar') }}" class="flex items-center justify-between px-4 py-2 text-gray-700 dark:text-gray-250 hover:bg-gray-50 dark:hover:bg-gray-800 {{ app()->getLocale() === 'ar' ? 'text-indigo-650 dark:text-indigo-400 font-bold' : '' }}">
                        <span>العربية</span>
                        @if(app()->getLocale() === 'ar') <svg class="w-3.5 h-3.5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> @endif
                    </a>
                    <a href="{{ route('lang.switch', 'en') }}" class="flex items-center justify-between px-4 py-2 text-gray-700 dark:text-gray-250 hover:bg-gray-50 dark:hover:bg-gray-800 {{ app()->getLocale() === 'en' ? 'text-indigo-650 dark:text-indigo-400 font-bold' : '' }}">
                        <span>English</span>
                        @if(app()->getLocale() === 'en') <svg class="w-3.5 h-3.5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> @endif
                    </a>
                </div>
            </div>

            <!-- Theme Switcher -->
            <button @click="darkMode = !darkMode" class="p-2.5 rounded-xl bg-white dark:bg-gray-800 shadow-md border cursor-pointer border-gray-100 dark:border-gray-700 hover:scale-105 transition duration-200">
                <template x-if="darkMode">
                    <!-- Sun icon -->
                    <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M14 12a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </template>
                <template x-if="!darkMode">
                    <!-- Moon icon -->
                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </template>
            </button>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-8 py-8 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden sm:rounded-2xl border border-gray-100 dark:border-gray-800/80 transition duration-300">
            <div class="flex flex-col items-center justify-center mb-8">
                <a href="/" class="flex flex-col items-center gap-1 hover:opacity-90 transition duration-300">
                    <svg class="h-12 w-12" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 2L30 16L16 30L2 16L16 2Z" class="fill-indigo-600 dark:fill-indigo-400"/>
                        <path d="M16 8L24 16L16 24L8 16L16 8Z" class="fill-white dark:fill-gray-900"/>
                        <path d="M16 13L19 16L16 19L13 16L16 13Z" class="fill-indigo-600 dark:fill-indigo-400"/>
                    </svg>
                    <div class="flex flex-col items-center">
                        <span class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white leading-none">ELEGANT</span>
                        <span class="text-xs font-semibold tracking-[0.3em] text-indigo-600 dark:text-indigo-400 leading-none">STORE</span>
                    </div>
                </a>
                <h2 class="mt-4 text-xl font-bold text-gray-800 dark:text-gray-200">
                    @php
                        $heading = match(Route::currentRouteName()) {
                            'register' => __('global.sign_up'),
                            'password.request' => __('global.reset_password'),
                            default => __('global.store_login'),
                        };
                    @endphp
                    {{ $heading }}
                </h2>
            </div>

            {{ $slot }}
        </div>

        @if(session('success'))
            <script>(()=>{document.addEventListener('DOMContentLoaded',()=>{setTimeout(()=>window.dispatchEvent(new CustomEvent('toast',{detail:{type:'success',message:@json(session('success'))}})),100)})})()</script>
        @endif
        @if(session('error'))
            <script>(()=>{document.addEventListener('DOMContentLoaded',()=>{setTimeout(()=>window.dispatchEvent(new CustomEvent('toast',{detail:{type:'error',message:@json(session('error'))}})),100)})})()</script>
        @endif
        @if($errors->any())
            <script>(()=>{document.addEventListener('DOMContentLoaded',()=>{setTimeout(()=>{@foreach($errors->all() as $error)window.dispatchEvent(new CustomEvent('toast',{detail:{type:'error',message:@json($error)}}));@endforeach},100)})})()</script>
        @endif

        <!-- Toast Notifications Component -->
        <div x-data="{ toasts: [] }"
             @toast.window="toasts.push($event.detail); setTimeout(() => toasts.shift(), 4500)"
             class="fixed bottom-5 left-5 space-y-3 z-50 max-w-sm">
            <template x-for="(toast, index) in toasts" :key="index">
                <div x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="backdrop-blur-md bg-white/95 dark:bg-gray-800/95 border border-gray-100 dark:border-gray-700 shadow-2xl rounded-2xl p-4 flex items-start gap-3 text-sm font-semibold text-gray-900 dark:text-white transition-all border-r-4"
                     :class="toast.type === 'error' ? 'border-r-rose-500' : 'border-r-emerald-500'">

                    <!-- Success Icon -->
                    <template x-if="toast.type !== 'error'">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </template>

                    <!-- Error Icon -->
                    <template x-if="toast.type === 'error'">
                        <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </template>

                    <div class="flex-grow">
                        <span x-text="toast.message" class="text-sm text-gray-800 dark:text-gray-200"></span>
                    </div>

                    <button @click="toasts.splice(index,1)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

    </body>
</html>
