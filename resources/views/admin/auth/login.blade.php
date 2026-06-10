<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
      x-data="{ darkMode: localStorage.getItem('darkMode') !== 'false', showPassword: false }"
      x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); document.documentElement.classList.toggle('dark', val) })"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('global.admin_login')) | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('darkMode') !== 'false') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .animate-float { animation: float 4s ease-in-out infinite; }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-950 flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 animate-float">
                <svg class="h-10 w-10" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 2L30 16L16 30L2 16L16 2Z" class="fill-indigo-600 dark:fill-indigo-400"/>
                    <path d="M16 8L24 16L16 24L8 16L16 8Z" class="fill-white dark:fill-gray-900"/>
                    <path d="M16 13L19 16L16 19L13 16L16 13Z" class="fill-indigo-600 dark:fill-indigo-400"/>
                </svg>
                <div class="flex flex-col items-start">
                    <span class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white leading-none">ELEGANT</span>
                    <span class="text-[10px] font-semibold tracking-[0.3em] text-indigo-600 dark:text-indigo-400 leading-none">STORE</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 p-8">
            <h2 class="text-2xl font-bold mb-2 text-center text-gray-900 dark:text-white">{{ __('global.admin_dashboard') }}</h2>
            <p class="text-center text-sm text-gray-500 dark:text-gray-400 mb-6">{{ __('global.admin_login_enter') }}</p>

            @if ($errors->any())
                <div class="mb-6 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/50 rounded-xl p-4 text-start">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            @foreach ($errors->all() as $error)
                                <p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if (session('status'))
                <div class="mb-6 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800/50 rounded-xl p-4 text-start">
                    <p class="text-sm text-green-700 dark:text-green-400">{{ session('status') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5 text-start">{{ __('global.email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5 text-start">{{ __('global.password') }}</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" name="password" required
                               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm" placeholder="********">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'left-0 pr-3' : 'right-0 pr-3' }} flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <svg x-show="showPassword" class="w-5 h-5" style="display:none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg hover:shadow-indigo-500/25 transition-all duration-300 text-sm">
                    {{ __('global.admin_login') }}
                </button>
            </form>
        </div>
    </div>
</body>
</html>
