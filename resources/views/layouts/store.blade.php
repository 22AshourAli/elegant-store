<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
      x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
      x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); document.documentElement.classList.toggle('dark', val) })"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4f46e5">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    <meta name="description" content="@yield('meta_description', __('global.hero_desc'))">
    <meta property="og:title" content="@yield('og_title', config('app.name'))">
    <meta property="og:description" content="@yield('og_description', __('global.hero_desc'))">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', config('app.name'))">
    <meta name="twitter:description" content="@yield('og_description', __('global.hero_desc'))">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og-default.jpg'))">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="ar" href="{{ url('/lang/ar') }}">
    <link rel="alternate" hreflang="en" href="{{ url('/lang/en') }}">
    <link rel="alternate" hreflang="x-default" href="{{ url('/') }}">
    {!! SEO::generate() !!}
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Cpath d='M16 2L30 16L16 30L2 16L16 2Z' fill='%234f46e5'/%3E%3Cpath d='M16 8L24 16L16 24L8 16L16 8Z' fill='%23ffffff'/%3E%3Cpath d='M16 13L19 16L16 19L13 16L16 13Z' fill='%234f46e5'/%3E%3C/svg%3E" />
    @vite('resources/css/app.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        html { scroll-behavior: smooth; }
    </style>
    @stack('seo')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Organization",
        "name": "Elegant Store",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('images/logo.svg') }}",
        "contactPoint": {
            "@@type": "ContactPoint",
            "telephone": "+{{ config('store.admin_phone') }}",
            "contactType": "customer service"
        }
    }
    </script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "url": "{{ url('/') }}",
        "potentialAction": {
            "@@type": "SearchAction",
            "target": {
                "@@type": "EntryPoint",
                "urlTemplate": "{{ url('/') }}?search={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        }
    }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-sans antialiased transition-colors duration-300" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

    <!-- Header/Navbar -->
    <header x-data="{
        mobileMenuOpen: false,
        cartCount: 0,
        fetchCartCount() {
            fetch('{{ route('cart.count') }}')
                .then(r => r.json())
                .then(data => { this.cartCount = data.count });
        }
    }" x-init="fetchCartCount()" @cart-updated.window="cartCount = $event.detail.count" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 sticky top-0 z-50 transition-colors shadow-sm">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <!-- الشعار اللوجو التفاعلي -->
            <a href="{{ route('home') }}" class="flex items-center gap-2 hover:opacity-90 transition">
                <svg class="h-9 w-9" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 2L30 16L16 30L2 16L16 2Z" class="fill-indigo-600 dark:fill-indigo-400"/>
                    <path d="M16 8L24 16L16 24L8 16L16 8Z" class="fill-white dark:fill-gray-900"/>
                    <path d="M16 13L19 16L16 19L13 16L16 13Z" class="fill-indigo-600 dark:fill-indigo-400"/>
                </svg>
                <div class="flex flex-col items-start">
                    <span class="text-xl font-extrabold tracking-tight text-gray-900 dark:text-white leading-none">ELEGANT</span>
                    <span class="text-[10px] font-semibold tracking-[0.3em] text-indigo-600 dark:text-indigo-400 leading-none">STORE</span>
                </div>
            </a>

            <!-- روابط سطح المكتب -->
            <nav class="hidden md:flex items-center gap-6">
                <a href="{{ route('home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition font-semibold text-sm">{{ __('global.home') }}</a>
                <a href="{{ route('shop.category', 'men-clothing') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition font-semibold text-sm">{{ __('global.mens_clothing') }}</a>
                <a href="{{ route('shop.category', 'men-pants') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition font-semibold text-sm">{{ __('global.pants') }}</a>
            </nav>

            <!-- أيقونات وقائمة الجوال -->
            <div class="flex items-center gap-3">
                <!-- اختيار اللغة -->
                <div class="relative" x-data="{ langOpen: false }">
                    <button @click="langOpen = !langOpen" class="flex items-center gap-1 p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full text-gray-600 dark:text-gray-300 transition focus:outline-none text-xs font-bold uppercase">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h1.5a3 3 0 003-3V6.7m-2 9l-3-3m0 0l-3 3m3-3v12M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                        <span class="hidden sm:inline">{{ app()->getLocale() === 'ar' ? 'العربية' : 'EN' }}</span>
                    </button>
                    <div x-show="langOpen" @click.away="langOpen = false" x-transition class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-32 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 py-2 z-50 text-start" style="display: none;">
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

                <!-- Dark mode -->
                <button @click="darkMode = !darkMode" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full text-gray-600 dark:text-gray-300 transition">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    <svg x-show="darkMode" style="display: none;" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"></circle><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"></path></svg>
                </button>

                <!-- السلة -->
                <a href="{{ route('cart.index') }}" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full text-gray-600 dark:text-gray-300 relative transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span x-show="cartCount > 0" x-text="cartCount" class="absolute -top-1 -right-1 bg-indigo-600 text-white text-xs font-bold rounded-full h-4 w-4 flex items-center justify-center animate-bounce" x-cloak></span>
                </a>

                @auth
                    <!-- Notifications Bell -->
                    <div x-data="{ open: false, unread: {{ auth()->user()->unreadNotifications->count() }} }" class="relative">
                        <button @click="open = !open" class="relative p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full text-gray-600 dark:text-gray-300 transition focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <span x-show="unread > 0" x-text="unread" class="absolute top-1 right-1 bg-red-500 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center animate-pulse" x-cloak></span>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-72 bg-white dark:bg-gray-800 shadow-xl rounded-xl border border-gray-100 dark:border-gray-700 py-2 z-50 text-start" style="display: none;">
                            <div class="px-4 py-2 border-b dark:border-gray-700 font-bold text-sm text-gray-900 dark:text-white">{{ __('global.notifications') }}</div>
                            <div class="max-h-60 overflow-y-auto divide-y dark:divide-gray-700">
                                @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                                    <div class="px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <p class="text-xs text-gray-800 dark:text-gray-200">{{ $notification->data['message'] ?? '' }}</p>
                                        <span class="text-[10px] text-gray-400 mt-1 block">{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>
                                @empty
                                    <div class="px-4 py-4 text-center text-xs text-gray-400">{{ __('global.no_unread_notifications') }}</div>
                                @endforelse
                            </div>
                            <a href="{{ route('notifications.index') }}" class="block text-center text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 pt-2 border-t dark:border-gray-700">{{ __('global.view_all_notifications') }}</a>
                        </div>
                    </div>

                    <!-- حساب المستخدم -->
                    <div class="relative" x-data="{ accountOpen: false }">
                        <button @click="accountOpen = !accountOpen" class="flex items-center gap-1.5 p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full text-gray-600 dark:text-gray-300 transition focus:outline-none">
                            @if(auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-7 h-7 rounded-full object-cover">
                            @else
                                <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 flex items-center justify-center font-bold text-xs">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                            @endif
                        </button>
                        <div x-show="accountOpen" @click.away="accountOpen = false" x-transition class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 py-2 z-50 text-start" style="display: none;">
                            <div class="px-4 py-1.5 border-b dark:border-gray-700 text-xs text-gray-400 font-semibold truncate">{{ auth()->user()->name }}</div>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('global.profile') }}</a>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isManager())
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('global.admin_dashboard') }}</a>
                            @else
                                <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('global.my_orders') }}</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="m-0 border-t dark:border-gray-700 mt-1.5">
                                @csrf
                                <button type="submit" class="w-full text-start px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('global.logout') }}</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn-primary text-xs py-2 px-4 font-bold rounded-lg shadow-md hover:shadow-lg transition">{{ __('global.login') }} / {{ __('global.register') }}</a>
                @endauth

                <!-- زر القائمة للجوال -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full text-gray-600 dark:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>
        </div>

        <!-- قائمة الجوال المنسدلة -->
        <div x-show="mobileMenuOpen" @click.away="mobileMenuOpen = false" x-transition class="md:hidden border-t dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 space-y-1 text-start" style="display: none;">
            <a href="{{ route('home') }}" class="block py-2 text-sm font-medium hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('global.home') }}</a>
            <a href="{{ route('shop.category', 'men-clothing') }}" class="block py-2 text-sm font-medium hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('global.mens_clothing') }}</a>
            <a href="{{ route('shop.category', 'men-pants') }}" class="block py-2 text-sm font-medium hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('global.pants') }}</a>
        </div>
    </header>

    <main class="min-h-screen">
        @yield('content')
    </main>

    <!-- الفوتر العصري -->
    <footer class="bg-gray-100 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 mt-12 transition-colors">
        <div class="container mx-auto px-4 py-12 grid md:grid-cols-3 gap-8 text-start">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <svg class="h-7 w-7" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 2L30 16L16 30L2 16L16 2Z" class="fill-indigo-600 dark:fill-indigo-400"/>
                        <path d="M16 8L24 16L16 24L8 16L16 8Z" class="fill-gray-100 dark:fill-gray-900"/>
                        <path d="M16 13L19 16L16 19L13 16L16 13Z" class="fill-indigo-600 dark:fill-indigo-400"/>
                    </svg>
                    <div class="flex flex-col items-start">
                        <span class="text-lg font-extrabold tracking-tight text-gray-900 dark:text-white leading-none">ELEGANT</span>
                        <span class="text-[9px] font-semibold tracking-[0.3em] text-indigo-600 dark:text-indigo-400 leading-none">STORE</span>
                    </div>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ __('global.footer_desc') }}</p>
            </div>
            <div>
                <h3 class="font-bold text-lg mb-4 text-indigo-600 dark:text-indigo-400">{{ __('global.quick_links') }}</h3>
                <ul class="space-y-2.5 text-sm text-gray-600 dark:text-gray-400">
                    <li><a href="#" class="hover:text-indigo-500 transition">{{ __('global.return_policy') }}</a></li>
                    <li><a href="#" class="hover:text-indigo-500 transition">{{ __('global.shipping_info') }}</a></li>
                    <li><a href="https://wa.me/201094022327" target="_blank" class="hover:text-indigo-500 transition font-bold text-indigo-600 dark:text-indigo-400">{{ __('global.contact_us') }}</a></li>
                </ul>
            </div>
            <div>
                <h3 class="font-bold text-lg mb-4 text-indigo-600 dark:text-indigo-400">{{ __('global.follow_us') }}</h3>
                <div class="flex flex-wrap gap-3 mt-2">
                    <a href="{{ config('store.whatsapp_url') }}" target="_blank" class="flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:text-green-500 transition text-xs font-semibold">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        <span>WhatsApp</span>
                    </a>
                    <a :href="'tel:+{{ config('store.admin_phone') }}'" class="flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:text-indigo-500 transition text-xs font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>Call</span>
                    </a>
                    <a target="_blank" href="{{ config('store.facebook') }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:text-blue-600 transition text-xs font-semibold">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        <span>{{ __('global.facebook') }}</span>
                    </a>
                    <a target="_blank" href="{{ config('store.instagram') }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:text-pink-600 transition text-xs font-semibold">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678a6.162 6.162 0 100 12.324 6.162 6.162 0 100-12.324zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405a1.441 1.441 0 11-2.882 0 1.441 1.441 0 012.882 0z"/></svg>
                        <span>{{ __('global.instagram') }}</span>
                    </a>
                    @if(config('store.admin_email'))
                        <a href="mailto:{{ config('store.admin_email') }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:text-indigo-500 transition text-xs font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>Email</span>
                        </a>
                    @endif
                </div>
                <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('global.phone') }}: <a href="tel:+{{ config('store.admin_phone') }}" class="font-semibold text-gray-700 dark:text-gray-200">+{{ config('store.admin_phone') }}</a>
                    @if(config('store.admin_email'))
                        <div>{{ __('global.email') }}: <a href="mailto:{{ config('store.admin_email') }}" class="font-semibold text-gray-700 dark:text-gray-200">{{ config('store.admin_email') }}</a></div>
                    @endif
                </div>
            </div>
        </div>
        <div class="text-center py-6 text-xs text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-800">
            <div>&copy; {{ date('Y') }} Elegant Store. {{ __('global.rights_reserved') }}</div>
            <div class="mt-1 text-gray-400 dark:text-gray-500">{!! __('global.built_by') !!}</div>
        </div>
    </footer>

    <!-- Toast Notifications Component -->
    <div x-data="{
            toasts: [],
            init() {
                @if(session('success'))
                    this.toasts.push({ type: 'success', message: {!! json_encode(session('success')) !!} });
                    setTimeout(() => this.toasts.shift(), 4500);
                @endif
                @if(session('error'))
                    this.toasts.push({ type: 'error', message: {!! json_encode(session('error')) !!} });
                    setTimeout(() => this.toasts.shift(), 4500);
                @endif
                @if($errors->any())
                    @foreach($errors->all() as $error)
                        this.toasts.push({ type: 'error', message: {!! json_encode($error) !!} });
                        setTimeout(() => this.toasts.shift(), 5500);
                    @endforeach
                @endif
            }
         }"
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

    <!-- GSAP Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            gsap.registerPlugin(ScrollTrigger);

            // Entrance animations for sections
            gsap.utils.toArray('.reveal').forEach((el) => {
                gsap.fromTo(el,
                    { opacity: 0, y: 30 },
                    {
                        opacity: 1,
                        y: 0,
                        duration: 0.8,
                        ease: 'power2.out',
                        scrollTrigger: {
                            trigger: el,
                            start: 'top 85%',
                            toggleActions: 'play none none none'
                        }
                    }
                );
            });

            // Stagger product cards appearance (one by one)
            const productCards = gsap.utils.toArray('.product-card');
            if (productCards.length > 0) {
                gsap.from(productCards, {
                    opacity: 0,
                    y: 30,
                    duration: 0.6,
                    stagger: 0.1,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: productCards[0].parentElement,
                        start: 'top 85%',
                        toggleActions: 'play none none none'
                    }
                });
            }

            // Hero slider entrance animations (first slide only)
            if (document.querySelector('.hero-title')) {
                gsap.from('.hero-title', { opacity: 0, y: -45, duration: 1, delay: 0.2, ease: 'back.out(1.7)' });
            }
            if (document.querySelector('.hero-desc')) {
                gsap.from('.hero-desc', { opacity: 0, y: 25, duration: 1, delay: 0.4, ease: 'power2.out' });
            }
            if (document.querySelector('.hero-btn')) {
                gsap.from('.hero-btn', { opacity: 0, scale: 0.85, duration: 1, delay: 0.6, ease: 'elastic.out(1, 0.5)' });
            }

            // Button hover animations (scale, shadow)
            gsap.utils.toArray('.btn-hover, .btn-primary, .product-card a[class*="rounded-full"]').forEach(btn => {
                btn.addEventListener('mouseenter', () => {
                    gsap.to(btn, { scale: 1.08, boxShadow: '0 10px 25px -5px rgba(0,0,0,0.2)', duration: 0.2, ease: 'power2.out', overwrite: 'auto' });
                });
                btn.addEventListener('mouseleave', () => {
                    gsap.to(btn, { scale: 1, boxShadow: 'none', duration: 0.2, ease: 'power2.out', overwrite: 'auto' });
                });
            });
        });
    </script>

    <!-- Back to Top Button -->
    <button x-data="{ show: false }"
            x-init="window.addEventListener('scroll', () => show = window.scrollY > 400)"
            x-show="show"
            x-transition
            @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-5 {{ app()->getLocale() === 'ar' ? 'left-5' : 'right-5' }} z-40 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-3 shadow-xl hover:shadow-2xl transition-all"
            style="display: none;">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
    </button>
</body>
</html>
