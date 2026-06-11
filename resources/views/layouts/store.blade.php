<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
      x-data="{
        darkMode: localStorage.getItem('darkMode') !== 'false',
        mobileOpen: false,
        cartCount: 0,
        wishlistCount: 0,
        notifCount: 0,
        searchQuery: '',
        init() {
            this.$watch('darkMode', val => {
                localStorage.setItem('darkMode', val);
                document.documentElement.classList.toggle('dark', val);
            });
            this.fetchCartCount();
            this.fetchWishlistCount();
            this.fetchNotifCount();
        },
        fetchCartCount() {
            fetch('{{ route('cart.count') }}').then(r => r.json()).then(d => this.cartCount = d.count);
        },
        fetchWishlistCount() {
            @auth
            fetch('{{ route('wishlist.count') }}').then(r => r.json()).then(d => this.wishlistCount = d.count).catch(() => {});
            @endauth
        },
        fetchNotifCount() {
            @auth
            fetch('{{ route('notifications.unread-count') }}').then(r => r.json()).then(d => this.notifCount = d.count).catch(() => {});
            @endauth
        }
      }"
      x-init="init()"
      @cart-updated.window="cartCount = $event.detail.count"
      @wishlist-updated.window="if($event.detail?.count !== undefined) wishlistCount = $event.detail.count; else fetchWishlistCount()"
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
    <meta name="color-scheme" content="dark light">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    <meta name="description" content="@yield('meta_description', __('global.hero_desc'))">
    <meta property="og:title" content="@yield('og_title', config('app.name'))">
    <meta property="og:description" content="@yield('og_description', __('global.hero_desc'))">
    <meta property="og:image" content="@yield('og_image', asset('images/logo.svg'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', config('app.name'))">
    <meta name="twitter:description" content="@yield('og_description', __('global.hero_desc'))">
    <meta name="twitter:image" content="@yield('og_image', asset('images/logo.svg'))">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="ar" href="{{ url('/lang/ar') }}">
    <link rel="alternate" hreflang="en" href="{{ url('/lang/en') }}">
    <link rel="alternate" hreflang="x-default" href="{{ url('/') }}">
    {!! SEO::generate() !!}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}body{margin:0;padding:0}</style>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    @stack('head')
    <script type="application/ld+json">
    [{
        "@@context": "https://schema.org",
        "@@type": "Organization",
        "name": "Elegant Store",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('images/logo.svg') }}",
        "contactPoint": { "@@type": "ContactPoint", "telephone": "+{{ config('store.admin_phone') }}", "contactType": "customer service" },
        "sameAs": ["{{ config('store.facebook') }}", "{{ config('store.instagram') }}"]
    },
    {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "url": "{{ url('/') }}",
        "potentialAction": {
            "@@type": "SearchAction",
            "target": { "@@type": "EntryPoint", "urlTemplate": "{{ url('/?search={search_term_string}') }}" },
            "query-input": "required name=search_term_string"
        }
    }]
    </script>
    @stack('schema')
</head>
<body class="bg-slate-50 dark:bg-[#030712] text-slate-900 dark:text-slate-100 font-sans antialiased selection:bg-indigo-500 selection:text-white" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

    <!-- Navbar (Semantic Header) -->
    <header role="banner" class="sticky top-0 z-50 glass-premium nav-blur border-b border-slate-200/40 dark:border-slate-900/60 shadow-[0_4px_30px_rgba(0,0,0,0.02)] dark:shadow-[0_10px_35px_rgba(0,0,0,0.3)] transition-all duration-300">
        <div class="container flex items-center justify-between h-14 sm:h-16 lg:h-20">

            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0 group focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none rounded-lg" aria-label="{{ config('app.name') }} Home">
                <svg class="h-8 w-8 transition-transform group-hover:scale-110 duration-300 shadow-sm rounded-lg" viewBox="0 0 32 32" fill="none">
                    <path d="M16 2L30 16L16 30L2 16L16 2Z" class="fill-brand-primary dark:fill-accent"/>
                    <path d="M16 8L24 16L16 24L8 16L16 8Z" class="fill-white dark:fill-bg-dark"/>
                    <path d="M16 13L19 16L16 19L13 16L16 13Z" class="fill-brand-primary dark:fill-accent"/>
                </svg>
                <div class="hidden sm:flex flex-col leading-none">
                    <span class="text-base font-extrabold tracking-tight text-slate-950 dark:text-transparent dark:bg-clip-text dark:bg-gradient-to-r dark:from-indigo-400 dark:via-purple-400 dark:to-indigo-300 uppercase">Elegant</span>
                    <span class="text-[9px] font-bold tracking-[0.35em] text-brand-primary dark:text-luxury-gold uppercase">Store</span>
                </div>
            </a>

            <!-- Desktop Search (Premium Input) -->
            <div class="hidden md:flex flex-1 max-w-md mx-6 lg:mx-10">
                <form action="{{ route('shop.search') }}" method="GET" class="relative w-full">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('global.search_placeholder') ?? 'Search products...' }}" aria-label="{{ __('global.search_placeholder') ?? 'Search products' }}"
                           class="w-full ps-12 pe-5 h-10 text-sm bg-slate-100/50 dark:bg-slate-950/40 border border-slate-200/50 dark:border-slate-900 rounded-full focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary dark:focus:border-accent dark:text-slate-100 placeholder-slate-400/80 transition-all duration-300 focus:bg-white dark:focus:bg-slate-950/80 focus:shadow-[0_0_15px_rgba(79,70,229,0.1)] dark:focus:shadow-[0_0_20px_rgba(139,92,246,0.15)] outline-none">
                    <button type="submit" class="absolute top-1/2 -translate-y-1/2 start-4 w-4 h-4 text-slate-400 hover:text-brand-primary dark:hover:text-accent transition-colors" aria-label="بحث">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Desktop Nav Links -->
            <nav role="navigation" aria-label="Main navigation" class="hidden lg:flex items-center gap-1.5">
                @foreach($navbarCategories as $cat)
                    @if(is_object($cat) && isset($cat->slug))
                        @if($cat->children && $cat->children->count() > 0)
                            <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="relative">
                                <a href="{{ route('shop.category', $cat->slug) }}"
                                   aria-label="Category: {{ $cat->name }}"
                                   aria-haspopup="true"
                                   :aria-expanded="open.toString()"
                                   class="nav-link-underline px-3.5 py-2.5 text-xs font-bold tracking-wide text-slate-700 dark:text-slate-350 hover:text-brand-primary dark:hover:text-accent rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900/55 transition-all duration-200 whitespace-nowrap flex items-center gap-1.5 focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none">
                                    {{ $cat->name }}
                                    <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </a>
                                <div x-show="open" x-cloak class="absolute top-full {{ app()->getLocale() === 'ar' ? 'right-0' : 'left-0' }} mt-2 w-52 bg-white/95 dark:bg-slate-950/80 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-900 py-2 z-50 glass-premium animate-scaleIn">
                                    @foreach($cat->children as $child)
                                        @if(is_object($child) && isset($child->slug))
                                            <a href="{{ route('shop.category', $child->slug) }}" class="block px-4.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-brand-primary dark:hover:text-accent hover:bg-slate-50/80 dark:hover:bg-slate-900/40 rounded-lg mx-1 transition-all duration-150 focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none">{{ $child->name }}</a>
                                        @endif
                                    @endforeach
                                    <div class="border-t border-slate-100 dark:border-slate-900 mt-1.5 pt-1.5">
                                        <a href="{{ route('shop.category', $cat->slug) }}" class="block px-4.5 py-2 text-xs font-bold text-brand-primary dark:text-accent hover:bg-slate-50/80 dark:hover:bg-slate-900/40 rounded-lg mx-1 transition-all duration-150 focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none">{{ __('global.view_all') }}</a>
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('shop.category', $cat->slug) }}" aria-label="Category: {{ $cat->name }}" class="nav-link-underline px-3.5 py-2.5 text-xs font-bold tracking-wide text-slate-700 dark:text-slate-350 hover:text-brand-primary dark:hover:text-accent rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900/55 transition-all duration-200 whitespace-nowrap focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none">{{ $cat->name }}</a>
                        @endif
                    @endif
                @endforeach
            </nav>

            <!-- Right Icons -->
            <div class="flex items-center gap-1 sm:gap-2">

                <!-- Mobile Search Toggle -->
                <div class="md:hidden" x-data="{ show: false }">
                    <button @click="show = !show" :aria-expanded="show.toString()" class="icon-btn focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none" aria-label="Search Toggle">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                    <div x-show="show" x-cloak @click.away="show = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="absolute top-full left-0 right-0 bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 p-4 shadow-lg z-50 glass-premium">
                        <form action="{{ route('shop.search') }}" method="GET" class="flex gap-2">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('global.search_placeholder') ?? 'Search...' }}"
                                   class="flex-1 px-4 h-11 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-750 rounded-2xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary dark:focus:border-accent dark:text-slate-100 outline-none">
                            <button type="submit" class="px-5 h-11 bg-brand-primary hover:bg-brand-hover text-white rounded-2xl text-sm font-bold shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Language Selection -->
                <div class="hidden lg:block relative" x-data="{ open: false }">
                    <button @click="open = !open" :aria-expanded="open.toString()" class="icon-btn text-xs font-bold gap-1 focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none" aria-label="Switch Language">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-.778.099-1.533.284-2.253"></path>
                        </svg>
                        <span class="hidden lg:inline">{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-32 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-800/80 py-2.5 z-50 glass-premium animate-scaleIn">
                        <a href="{{ route('lang.switch', 'ar') }}" class="flex items-center justify-between px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/60 rounded-lg mx-1 transition-all {{ app()->getLocale() === 'ar' ? 'text-brand-primary dark:text-accent bg-indigo-50/50 dark:bg-indigo-950/20' : '' }}">
                            العربية
                            @if(app()->getLocale() === 'ar') <svg class="w-3.5 h-3.5 text-brand-primary dark:text-accent" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> @endif
                        </a>
                        <a href="{{ route('lang.switch', 'en') }}" class="flex items-center justify-between px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/60 rounded-lg mx-1 transition-all {{ app()->getLocale() === 'en' ? 'text-brand-primary dark:text-accent bg-indigo-50/50 dark:bg-indigo-950/20' : '' }}">
                            English
                            @if(app()->getLocale() === 'en') <svg class="w-3.5 h-3.5 text-brand-primary dark:text-accent" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> @endif
                        </a>
                    </div>
                </div>

                <!-- Dark Mode toggle button -->
                <div class="hidden lg:block">
                    <button @click="darkMode = !darkMode" aria-label="Toggle dark mode" class="icon-btn focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none">
                        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="5"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                    </button>
                </div>

                @auth
                <!-- Notifications Dropdown -->
                <div x-data="notifications()" x-init="init()" class="relative">
                    <button @click="togglePanel" class="icon-btn relative focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none" title="{{ __('global.notifications') }}" aria-label="View notifications" :aria-expanded="open.toString()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                        <span x-show="unread > 0" x-text="unread" class="badge-indigo animate-scaleIn" style="display:none"></span>
                    </button>

                    <div x-show="open" @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="fixed sm:absolute z-50 top-16 sm:top-auto inset-x-2 sm:inset-x-auto sm:mt-3.5 {{ app()->getLocale() === 'ar' ? 'sm:left-0' : 'sm:right-0' }} w-auto sm:w-85 bg-white/95 dark:bg-slate-950/90 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-900 max-h-96 overflow-hidden glass-premium"
                         style="display:none" role="dialog" aria-label="Notification Center">
                        <div class="p-4 border-b border-slate-100 dark:border-slate-900 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ __('global.notifications') }}</span>
                            <button x-show="unread > 0" @click="markAllRead" class="text-xs text-brand-primary dark:text-accent font-bold hover:underline flex-shrink-0 focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none">{{ __('global.admin_mark_all_read') }}</button>
                        </div>
                        <div class="overflow-y-auto max-h-72">
                            <div x-show="items.length === 0" class="p-8 text-center text-slate-400 text-sm" style="display:none">{{ __('global.admin_no_notifications') }}</div>
                            <template x-for="n in items" :key="n.id">
                                <a :href="n.url || '#'" @click="n.read_at ? null : markRead(n.id)" class="block px-4.5 py-3.5 hover:bg-slate-50/80 dark:hover:bg-slate-900/50 transition border-b border-slate-50 dark:border-slate-900/30 focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none" :class="{'bg-indigo-50/30 dark:bg-indigo-950/10': !n.read_at}">
                                    <div class="flex items-start gap-3.5">
                                        <div class="flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center shadow-sm" :class="n.type === 'exchange' ? 'bg-emerald-100 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400' : n.type === 'return' ? 'bg-amber-100 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400' : n.type === 'order' ? 'bg-indigo-100 dark:bg-indigo-950/30 text-brand-primary dark:text-accent' : 'bg-slate-100 dark:bg-slate-800 text-slate-500'">
                                            <template x-if="n.type === 'exchange'">
                                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                            </template>
                                            <template x-if="n.type === 'return'">
                                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3"/></svg>
                                            </template>
                                            <template x-if="n.type === 'order'">
                                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            </template>
                                            <template x-if="n.type === 'info'">
                                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
                                            </template>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="n.title"></p>
                                            <p class="text-[10px] font-medium text-slate-400 dark:text-slate-500 mt-0.5" x-text="n.time"></p>
                                        </div>
                                    </div>
                                </a>
                            </template>
                        </div>
                        <a href="{{ route('notifications.index') }}" class="block p-3.5 text-center text-xs font-bold text-brand-primary dark:text-accent hover:bg-slate-50 dark:hover:bg-slate-900/40 transition border-t border-slate-100 dark:border-slate-900 focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none">
                            @lang('global.admin_notifications_all')
                        </a>
                    </div>
                </div>
                @endauth

                <!-- Wishlist -->
                <a href="{{ auth()->check() ? route('wishlist.index') : route('login', ['redirect' => url()->current()]) }}" class="icon-btn relative hidden lg:inline-flex focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none" title="{{ __('global.wishlist') ?? 'Wishlist' }}" aria-label="View wishlist">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                    <span x-show="wishlistCount > 0" x-cloak x-text="wishlistCount" class="badge-indigo animate-scaleIn"></span>
                </a>

                <!-- Cart -->
                <a href="{{ route('cart.index') }}" class="icon-btn relative hidden lg:inline-flex focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none" title="{{ __('global.cart') ?? 'Cart' }}" aria-label="View cart">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                    <span x-show="cartCount > 0" x-cloak x-text="cartCount" class="badge-indigo animate-scaleIn"></span>
                </a>

                <!-- User Menu (Desktop) -->
                @auth
                <div x-data="{ open: false }" class="relative hidden sm:block">
                    <button @click="open = !open" :aria-expanded="open.toString()" class="icon-btn focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none" title="{{ auth()->user()->name }}" aria-label="User Profile menu">
                        @php $au = auth()->user(); @endphp
                        @if($au->avatar)
                            <img src="{{ $au->avatarUrl() }}" alt="" class="w-6 h-6 rounded-full object-cover ring-2 ring-brand-primary/20 hover:ring-brand-primary transition duration-300">
                        @else
                            <div class="w-6 h-6 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-brand-primary dark:text-accent flex items-center justify-center font-extrabold text-xs border border-indigo-200/50 dark:border-indigo-850/30">
                                {{ substr($au->name, 0, 1) }}
                            </div>
                        @endif
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-52 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-800/80 py-2.5 z-50 glass-premium animate-scaleIn">
                        <div class="px-4 py-2 border-b dark:border-slate-800 text-[10px] text-slate-400 font-bold uppercase tracking-wider truncate">{{ auth()->user()->name }}</div>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-all rounded-lg mx-1 focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none">{{ __('global.profile') }}</a>
                        <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-all rounded-lg mx-1 focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none">{{ __('global.my_orders') }}</a>
                        @if(auth()->user()->returnRequests()->exists())
                        <a href="{{ route('returns.index') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-all rounded-lg mx-1 focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none">{{ __('return.my_returns') }}</a>
                        @endif
                        @if(auth()->user()->exchanges()->exists())
                        <a href="{{ route('exchanges.index') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-all rounded-lg mx-1 focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none">{{ __('return.my_exchanges') }}</a>
                        @endif
                        <a href="{{ route('notifications.index') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-all rounded-lg mx-1 focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none">{{ __('global.notifications') }}</a>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isManager())
                            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-xs font-bold text-brand-primary dark:text-accent hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-all rounded-lg mx-1 focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none">{{ __('global.admin_dashboard') }}</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 dark:border-slate-800 mt-2 pt-2">
                            @csrf
                            <button type="submit" class="w-full cursor-pointer text-start px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition rounded-lg focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:outline-none">{{ __('global.logout') }}</button>
                        </form>
                    </div>
                </div>
                @else
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center h-9 px-4 bg-brand-primary hover:bg-brand-hover text-white text-[11px] sm:text-xs font-bold rounded-xl transition duration-300 shadow-[0_4px_10px_rgba(79,70,229,0.25)] hover:shadow-[0_4px_14px_rgba(79,70,229,0.45)] focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none">{{ __('global.login') }}</a>
                @endauth

                <!-- Hamburger -->
                <button @click="mobileOpen = true" aria-label="Open menu" :aria-expanded="mobileOpen.toString()" class="lg:hidden p-2 hover:bg-slate-150 dark:hover:bg-slate-900 rounded-full text-slate-600 dark:text-slate-300 transition duration-200 touch-target focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Off-canvas Overlay -->
    <div @click="mobileOpen = false"
         class="fixed inset-0 z-[55] bg-black/60 backdrop-blur-md lg:hidden transition-opacity duration-300 ease-out opacity-0 pointer-events-none"
         :class="{ 'opacity-100 pointer-events-auto': mobileOpen }"
         role="presentation">
    </div>

    <!-- Mobile Off-canvas Panel -->
    <aside role="dialog" aria-label="Mobile navigation menu"
           :aria-modal="mobileOpen.toString()"
           :aria-hidden="(!mobileOpen).toString()"
           class="fixed inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0' : 'left-0' }} z-[60] w-80 max-w-[85vw] bg-white/95 dark:bg-[#030712]/95 backdrop-blur-2xl shadow-2xl flex flex-col lg:hidden border-slate-100/60 dark:border-slate-900/40 {{ app()->getLocale() === 'ar' ? 'border-l' : 'border-r' }}
                  transition-all duration-[400ms] ease-[cubic-bezier(0.16,1,0.3,1)]
                  {{ app()->getLocale() === 'ar' ? 'translate-x-full' : '-translate-x-full' }}"
           :class="{ 'translate-x-0': mobileOpen }"
           @keydown.escape.window="mobileOpen = false">

            <!-- Drawer Header -->
            <div class="flex items-center justify-between p-4.5 border-b border-slate-100 dark:border-slate-900 shrink-0">
                <div class="flex items-center gap-2">
                    <svg class="h-6 w-6" viewBox="0 0 32 32" fill="none"><path d="M16 2L30 16L16 30L2 16L16 2Z" class="fill-brand-primary dark:fill-accent"/><path d="M16 8L24 16L16 24L8 16L16 8Z" class="fill-white dark:fill-bg-dark"/><path d="M16 13L19 16L16 19L13 16L16 13Z" class="fill-brand-primary dark:fill-accent"/></svg>
                    <span class="font-bold text-sm text-slate-950 dark:text-transparent dark:bg-clip-text dark:bg-gradient-to-r dark:from-indigo-400 dark:to-indigo-300">ELEGANT</span>
                </div>
                <button @click="mobileOpen = false" aria-label="Close menu" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-900 rounded-xl text-slate-500 transition focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Drawer Search -->
            <div class="p-4 border-b border-slate-100 dark:border-slate-900">
                <form action="{{ route('shop.search') }}" method="GET">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('global.search_placeholder') ?? 'Search...' }}"
                               class="w-full ps-10 pe-4 h-10 text-sm bg-slate-100/50 dark:bg-slate-950/40 border border-slate-200/50 dark:border-slate-900 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary dark:focus:border-accent dark:text-slate-150 transition-all duration-300 outline-none">
                        <button type="submit" class="absolute top-1/2 -translate-y-1/2 {{ app()->getLocale() === 'ar' ? 'right-3.5' : 'left-3.5' }} w-4 h-4 text-slate-400 hover:text-brand-primary dark:hover:text-accent transition-colors" aria-label="بحث">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Drawer User Info -->
            @auth
            @php $au = auth()->user(); @endphp
            <div class="p-4.5 border-b border-slate-100 dark:border-slate-900 flex items-center gap-3 bg-slate-50/50 dark:bg-slate-900/10">
                @if($au->avatar)
                    <img src="{{ $au->avatarUrl() }}" alt="" class="w-10 h-10 rounded-xl object-cover ring-2 ring-indigo-500/10">
                @else
                    <div class="w-10 h-10 rounded-xl bg-indigo-55 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm border border-indigo-200/40">
                        {{ substr($au->name, 0, 1) }}
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <div class="font-bold text-sm text-slate-900 dark:text-white truncate">{{ auth()->user()->name }}</div>
                    <div class="text-[10px] font-medium text-slate-400 truncate">{{ auth()->user()->email }}</div>
                </div>
            </div>
            @else
            <div class="p-4.5 border-b border-slate-100 dark:border-slate-900">
                <a href="{{ route('login') }}" class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl text-xs tracking-wide transition shadow-md">{{ __('global.login') }} / {{ __('global.register') }}</a>
            </div>
            @endauth

            <!-- Drawer Categories -->
            <div role="navigation" aria-label="Mobile menu navigation links" class="flex-1 overflow-y-auto py-3 no-scrollbar">
                <div class="px-4.5 py-2 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">{{ __('global.categories') ?? 'Categories' }}</div>
                <a href="{{ route('home') }}" class="flex items-center gap-3.5 px-4.5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-900 transition">
                    <svg class="w-4.5 h-4.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    {{ __('global.home') }}
                </a>
                @foreach($navbarCategories as $cat)
                    @if(is_object($cat) && isset($cat->slug))
                        @if($cat->children && $cat->children->count() > 0)
                            <div x-data="{ open: false }">
                                <button @click="open = !open" :aria-expanded="open.toString()" class="w-full flex items-center justify-between px-4.5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-900 transition">
                                    <span>{{ $cat->name }}</span>
                                    <svg class="w-4 h-4 text-slate-400 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" x-cloak class="bg-slate-55/30 dark:bg-slate-900/40 rounded-xl mx-2 py-1">
                                    @foreach($cat->children as $child)
                                        @if(is_object($child) && isset($child->slug))
                                            <a href="{{ route('shop.category', $child->slug) }}" class="block px-8 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">{{ $child->name }}</a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ route('shop.category', $cat->slug) }}" class="w-full flex items-center justify-between px-4.5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-900 transition">
                                <span>{{ $cat->name }}</span>
                            </a>
                        @endif
                    @endif
                @endforeach
            </div>

            <!-- Drawer Footer -->
            <div class="border-t border-slate-100 dark:border-slate-900 p-4 space-y-1.5 shrink-0 bg-slate-50/20 dark:bg-slate-950/20">
                @auth
                <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white transition rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900">
                    <svg class="w-4.5 h-4.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    {{ __('global.my_orders') }}
                </a>
                @if(auth()->user()->returnRequests()->exists())
                <a href="{{ route('returns.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white transition rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900">
                    <svg class="w-4.5 h-4.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    {{ __('return.my_returns') }}
                </a>
                @endif
                @if(auth()->user()->exchanges()->exists())
                <a href="{{ route('exchanges.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white transition rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900">
                    <svg class="w-4.5 h-4.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    {{ __('return.my_exchanges') }}
                </a>
                @endif
                <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white transition rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900">
                    <svg class="w-4.5 h-4.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    {{ __('global.notifications') }}
                </a>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white transition rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900">
                    <svg class="w-4.5 h-4.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    {{ __('global.profile') }}
                </a>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isManager())
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 transition rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950/20">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        {{ __('global.admin_dashboard') }}
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="pt-2 border-t border-slate-100 dark:border-slate-900 mt-2">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/10 transition rounded-xl">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        {{ __('global.logout') }}
                    </button>
                </form>
                @endauth
            </div>
    </aside>

    <!-- Mobile Bottom Navigation Bar -->
    <div class="fixed bottom-0 inset-x-0 z-40 bg-white/95 dark:bg-slate-950/95 backdrop-blur-2xl border-t border-slate-200/50 dark:border-slate-800/50 lg:hidden shadow-[0_-2px_20px_rgba(0,0,0,0.05)] dark:shadow-[0_-2px_30px_rgba(0,0,0,0.25)] overflow-x-hidden">
        <div class="grid grid-cols-5 py-1">
            <a href="{{ route('home') }}" class="flex flex-col items-center justify-center gap-0 min-w-0 text-slate-500 dark:text-slate-400 transition-colors rounded-lg {{ request()->routeIs('home') && !request()->has('search') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-950/30' : 'hover:bg-slate-100 dark:hover:bg-slate-900/50' }}">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                <span class="text-[8px] font-bold leading-tight">{{ __('global.home') }}</span>
            </a>
            <a href="{{ route('home') }}#featured" class="flex flex-col items-center justify-center gap-0 min-w-0 text-slate-500 dark:text-slate-400 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-900/50">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                <span class="text-[8px] font-bold leading-tight">{{ __('global.shop') }}</span>
            </a>
            <button @click="darkMode = !darkMode" class="flex flex-col items-center justify-center gap-0 min-w-0 text-slate-500 dark:text-slate-400 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-900/50 cursor-pointer">
                <svg x-show="!darkMode" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
                <svg x-show="darkMode" x-cloak class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
                <span class="text-[8px] font-bold leading-tight">{{ app()->getLocale() === 'ar' ? 'ليلي' : 'Dark' }}</span>
            </button>
            <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="flex flex-col items-center justify-center gap-0 min-w-0 text-slate-500 dark:text-slate-400 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-900/50">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.78.147 2.653.255m-4.589 8.495a18.023 18.023 0 01-3.827-5.802"/></svg>
                <span class="text-[8px] font-bold leading-tight">{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</span>
            </a>
            @auth
                <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center gap-0 min-w-0 text-slate-500 dark:text-slate-400 transition-colors rounded-lg {{ request()->routeIs('profile.edit') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-950/30' : 'hover:bg-slate-100 dark:hover:bg-slate-900/50' }}">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    <span class="text-[8px] font-bold leading-tight">{{ __('global.profile') }}</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="flex flex-col items-center justify-center gap-0 min-w-0 text-slate-500 dark:text-slate-400 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-900/50">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                    <span class="text-[8px] font-bold leading-tight">{{ __('global.login') }}</span>
                </a>
            @endauth
        </div>
    </div>

    <!-- Main Content Area -->
    <main role="main" class="min-h-screen pb-20 lg:pb-8">
        @yield('content')
    </main>

    <!-- Footer (Semantic contentinfo) -->
    <footer role="contentinfo" class="bg-white dark:bg-slate-950 border-t border-slate-100 dark:border-slate-900 transition-colors relative overflow-hidden">
        <div class="container py-12 sm:py-16">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">

                <!-- Brand -->
                <div class="sm:col-span-2 lg:col-span-1">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="h-7 w-7 shadow-sm rounded-lg" viewBox="0 0 32 32" fill="none"><path d="M16 2L30 16L16 30L2 16L16 2Z" class="fill-indigo-600 dark:fill-indigo-400"/><path d="M16 8L24 16L16 24L8 16L16 8Z" class="fill-white dark:fill-slate-900"/><path d="M16 13L19 16L16 19L13 16L16 13Z" class="fill-indigo-600 dark:fill-indigo-400"/></svg>
                        <div class="flex flex-col leading-none">
                            <span class="text-base font-extrabold tracking-tight text-slate-950 dark:text-white uppercase">ELEGANT</span>
                            <span class="text-[9px] font-bold tracking-[0.3em] text-indigo-600 dark:text-indigo-400 uppercase">STORE</span>
                        </div>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed font-medium">{{ __('global.footer_desc') }}</p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="font-bold text-sm mb-5 text-slate-900 dark:text-white uppercase tracking-wider">{{ __('global.quick_links') }}</h3>
                    <ul class="space-y-3 text-sm text-slate-500 dark:text-slate-400 font-semibold">
                        <li><a href="{{ route('home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">{{ __('global.home') }}</a></li>
                        <li><a href="{{ route('return.policy') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">{{ __('global.return_policy') }}</a></li>
                    </ul>
                </div>

                <!-- Categories -->
                <div>
                    <h3 class="font-bold text-sm mb-5 text-slate-900 dark:text-white uppercase tracking-wider">{{ __('global.categories') ?? 'Categories' }}</h3>
                    <ul class="space-y-3 text-sm text-slate-500 dark:text-slate-400 font-semibold">
                        @foreach($navbarCategories->take(5) as $cat)
                            @if(is_object($cat) && isset($cat->slug))
                                <li><a href="{{ route('shop.category', $cat->slug) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">{{ $cat->name }}</a></li>
                            @endif
                        @endforeach
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="font-bold text-sm mb-5 text-slate-900 dark:text-white uppercase tracking-wider">{{ __('global.contact_us') }}</h3>
                    <div class="space-y-3 text-sm text-slate-500 dark:text-slate-400 font-semibold">
                        <a href="tel:+{{ config('store.admin_phone') }}" class="flex items-center gap-2.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                            <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>+{{ config('store.admin_phone') }}</span>
                        </a>
                        <a href="{{ config('store.whatsapp_url') }}" target="_blank" class="flex items-center gap-2.5 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors duration-200">
                            <svg class="w-4 h-4 shrink-0 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
                            <span>WhatsApp</span>
                        </a>
                        @if(config('store.admin_email'))
                            <a href="mailto:{{ config('store.admin_email') }}" class="flex items-center gap-2.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                                <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span>Email</span>
                            </a>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2.5 mt-5">
                        <a target="_blank" href="{{ config('store.facebook') }}" class="p-2.5 bg-slate-50 dark:bg-slate-900 hover:bg-indigo-50 dark:hover:bg-indigo-950/20 rounded-xl text-slate-500 hover:text-indigo-600 transition-all duration-200 hover:scale-105" aria-label="Facebook Page">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a target="_blank" href="{{ config('store.instagram') }}" class="p-2.5 bg-slate-50 dark:bg-slate-900 hover:bg-rose-50 dark:hover:bg-rose-955/20 rounded-xl text-slate-500 hover:text-rose-500 transition-all duration-200 hover:scale-105" aria-label="Instagram Profile">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678a6.162 6.162 0 100 12.324 6.162 6.162 0 100-12.324zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405a1.441 1.441 0 11-2.882 0 1.441 1.441 0 012.882 0z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center py-6 pb-20 lg:pb-6 text-xs text-slate-400 dark:text-slate-500 border-t border-slate-100 dark:border-slate-900">
            <div>&copy; {{ date('Y') }} Elegant Store. {{ __('global.rights_reserved') }}</div>
        </div>
    </footer>

    <!-- Toast -->
    <aside aria-live="polite" aria-label="Notifications" x-data="{
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
    }" @toast.window="toasts.push($event.detail); setTimeout(() => toasts.shift(), 4500)"
       class="fixed bottom-20 lg:bottom-5 left-3 lg:left-5 right-3 lg:right-auto space-y-2 z-50 max-w-sm w-auto pointer-events-none">
        <template x-for="(toast, index) in toasts" :key="index">
            <div x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="pointer-events-auto backdrop-blur-md bg-white/95 dark:bg-gray-800/95 border shadow-2xl rounded-2xl p-4 flex items-start gap-3 text-sm font-semibold text-gray-900 dark:text-white border-r-4"
                 :class="toast.type === 'error' ? 'border-r-rose-500' : 'border-r-emerald-500'">
                <template x-if="toast.type !== 'error'">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
                <div class="flex-1 min-w-0"><span x-text="toast.message" class="text-sm text-gray-800 dark:text-gray-200"></span></div>
                <button @click="toasts.splice(index,1)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </template>
    </aside>

    <!-- Scroll Animations -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        if (entry.target.classList.contains('stagger')) {
                            entry.target.classList.add('in');
                        } else {
                            entry.target.classList.add('in');
                        }
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.08, rootMargin: '0px 0px -50px 0px' });

            document.querySelectorAll('.anim-fade-up, .anim-fade-in, .anim-scale, .anim-slide-right, .anim-slide-left, .stagger').forEach(el => observer.observe(el));
        });
    </script>

    <!-- Back to Top -->
    <button x-data="{ show: false }"
            x-init="window.addEventListener('scroll', () => show = window.scrollY > 400)"
            x-show="show" x-cloak
            x-transition
            @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-24 lg:bottom-6 {{ app()->getLocale() === 'ar' ? 'left-3 lg:left-6' : 'right-3 lg:right-6' }} z-40 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-2.5 sm:p-3 shadow-xl hover:shadow-2xl transition-all hover:scale-110">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
    </button>

    <script> if ('serviceWorker' in navigator) { navigator.serviceWorker.register(@json(asset('sw.js'))); } </script>

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
                _initialized: false,
                lastCount: 0,
                items: [],
                async init() {
                    await this.fetchUnread();
                    await this.fetchItems();
                    setInterval(() => this.fetchUnread(), 30000);
                },
                async fetchUnread() {
                    try {
                        const res = await fetch("{{ route('notifications.unread-count') }}");
                        const data = await res.json();
                        if (this._initialized && data.count > this.lastCount) playNotifSound();
                        this._initialized = true;
                        this.lastCount = data.count;
                        this.unread = data.count;
                    } catch(e) {}
                },
                async fetchItems() {
                    try {
                        const res = await fetch("{{ route('notifications.index') }}?json=1");
                        const data = await res.json();
                        this.items = data.notifications || [];
                    } catch(e) {}
                },
                togglePanel() {
                    this.open = !this.open;
                    if (this.open) this.fetchItems();
                },
                async markRead(id) {
                    try {
                        await fetch("{{ url('notifications') }}/" + id + "/read", { method: "POST", headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" } });
                        this.unread = Math.max(0, this.unread - 1);
                        const n = this.items.find(i => i.id === id);
                        if (n) n.read_at = true;
                    } catch(e) {}
                },
                async markAllRead() {
                    try {
                        await fetch("{{ route('notifications.mark-all-read') }}", { method: "POST", headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" } });
                        this.unread = 0;
                        this.items.forEach(n => n.read_at = true);
                    } catch(e) {}
                }
            }));
        });
    </script>
    @endpush

    @stack('scripts')
</body>
</html>
