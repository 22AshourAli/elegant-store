@extends('layouts.store')

@section('content')
<main class="container py-10 md:py-16 mb-10" x-data="cartView({{ json_encode($cartItems) }}, '{{ csrf_token() }}', {{ $cart->total() }}, {{ $shipping ?? 0 }})">

    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mb-8 tracking-tight">{{ __('global.shopping_cart') }}</h1>

    <div class="grid lg:grid-cols-3 gap-8">

        {{-- Cart Items --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Empty state --}}
            <template x-if="items.length === 0">
                <div class="bg-white/60 dark:bg-surface-dark/60 rounded-2xl p-14 text-center border border-slate-200/40 dark:border-slate-800/40 shadow-sm backdrop-blur-md">
                    <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800/60 rounded-full flex items-center justify-center mx-auto mb-5 text-slate-400 dark:text-slate-600">
                        <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-800 dark:text-slate-200 mb-2">{{ __('global.empty_cart') }}</h3>
                    <a href="{{ route('home') }}" class="mt-5 inline-flex items-center gap-2 btn-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        {{ __('global.home') }}
                    </a>
                </div>
            </template>

            {{-- Item list --}}
            <template x-if="items.length > 0">
                <div class="space-y-4">
                    <template x-for="item in items" :key="item.variant_id">
                        <article x-transition:enter="transition ease-out duration-500"
                             x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-300"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                             class="bg-white/70 dark:bg-surface-dark/60 rounded-2xl p-4 md:p-5 border border-slate-200/40 dark:border-slate-800/40 shadow-sm hover:shadow-md hover:border-brand-primary/20 dark:hover:border-accent/20 transition-all duration-300 flex flex-col sm:flex-row items-start sm:items-center gap-4 backdrop-blur-sm">

                            {{-- Product Image --}}
                            <img :src="item.image || '{{ asset('images/logo.svg') }}'"
                                 loading="lazy"
                                 :alt="item.product_name"
                                 class="w-20 h-24 object-cover rounded-xl border border-slate-200/40 dark:border-slate-800/40 flex-shrink-0">

                            {{-- Product Info --}}
                            <div class="flex-1 min-w-0 text-start">
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white truncate" x-text="item.product_name"></h3>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 flex flex-wrap gap-2">
                                    <span x-show="item.color" class="inline-flex items-center gap-1">
                                        <span class="font-semibold text-slate-500 dark:text-slate-400">{{ __('global.color') }}:</span>
                                        <span x-text="item.color" class="text-slate-700 dark:text-slate-300"></span>
                                    </span>
                                    <span x-show="item.color && item.size" class="text-slate-300 dark:text-slate-700">|</span>
                                    <span x-show="item.size" class="inline-flex items-center gap-1">
                                        <span class="font-semibold text-slate-500 dark:text-slate-400">{{ __('global.size') }}:</span>
                                        <span x-text="item.size" class="text-slate-700 dark:text-slate-300"></span>
                                    </span>
                                </p>
                                <p class="text-brand-primary dark:text-accent font-extrabold mt-2 text-sm" x-text="formatPrice(item.price)"></p>
                            </div>

                            {{-- Actions & Qty --}}
                            <div class="flex items-center justify-between sm:justify-end gap-4 w-full sm:w-auto mt-3 sm:mt-0 pt-3 sm:pt-0 border-t sm:border-0 border-slate-100 dark:border-slate-800/60">

                                {{-- Quantity Stepper --}}
                                <div class="flex items-center border border-slate-200/60 dark:border-slate-800/80 rounded-xl bg-white/80 dark:bg-slate-950/80 overflow-hidden shadow-sm hover:border-brand-primary/40 dark:hover:border-accent/40 transition-colors duration-200 opacity-75"
                                     :class="{ 'opacity-50 pointer-events-none': isItemLoading(item.variant_id) === 'qty' }"
                                     role="group" :aria-label="'Quantity for ' + item.product_name">
                                    <button @click="updateQty(item.variant_id, item.quantity - 1)"
                                            :disabled="isItemLoading(item.variant_id)"
                                            :aria-label="'Decrease quantity of ' + item.product_name"
                                            class="px-3 py-1.5 text-slate-500 hover:text-brand-primary hover:bg-brand-primary/5 dark:hover:bg-accent/5 focus-visible:outline-none transition-all duration-200 font-extrabold text-base cursor-pointer disabled:opacity-40">−</button>
                                    <span x-show="isItemLoading(item.variant_id) === 'qty'" class="w-9 flex justify-center">
                                        <svg class="w-4 h-4 animate-spin text-brand-primary dark:text-accent" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </span>
                                    <input x-show="!isItemLoading(item.variant_id)" type="number" x-model.number="item.quantity" @change="updateQty(item.variant_id, item.quantity)" :aria-label="'Quantity of ' + item.product_name"
                                           class="w-9 text-center bg-transparent border-0 focus:ring-0 p-0 font-black text-sm text-slate-900 dark:text-slate-100" min="1">
                                    <button @click="updateQty(item.variant_id, item.quantity + 1)"
                                            :disabled="isItemLoading(item.variant_id)"
                                            :aria-label="'Increase quantity of ' + item.product_name"
                                            class="px-3 py-1.5 text-slate-500 hover:text-brand-primary hover:bg-brand-primary/5 dark:hover:bg-accent/5 focus-visible:outline-none transition-all duration-200 font-extrabold text-base cursor-pointer disabled:opacity-40">+</button>
                                </div>

                                {{-- Line total --}}
                                <div class="text-start sm:text-end min-w-[72px]">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('global.total') }}</p>
                                    <p class="font-extrabold text-slate-900 dark:text-white text-sm" x-text="formatPrice(item.price * item.quantity)"></p>
                                </div>

                                {{-- Remove --}}
                                <button @click="removeItem(item.variant_id)"
                                        :disabled="isItemLoading(item.variant_id)"
                                        :aria-label="'Remove ' + item.product_name + ' from cart'"
                                        class="p-2 rounded-xl text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition-all duration-200 hover:scale-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 cursor-pointer disabled:opacity-40 disabled:hover:scale-100">
                                    <svg x-show="isItemLoading(item.variant_id) !== 'remove'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    <svg x-show="isItemLoading(item.variant_id) === 'remove'" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </button>
                            </div>
                        </article>
                    </template>
                </div>
            </template>
        </div>

        {{-- Order Summary Sidebar --}}
        <aside class="lg:col-span-1" x-show="items.length > 0"
               x-transition:enter="transition ease-out duration-500"
               x-transition:enter-start="opacity-0 translate-y-8 scale-95"
               x-transition:enter-end="opacity-100 translate-y-0 scale-100"
               aria-label="Order Summary">
            <div class="glass-premium rounded-2xl p-6 border border-slate-200/40 dark:border-slate-800/40 shadow-[0_8px_32px_0_rgba(31,38,135,0.06)] dark:shadow-[0_8px_32px_0_rgba(0,0,0,0.4)] sticky top-24">
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white mb-5 pb-4 border-b border-slate-200/40 dark:border-slate-800/60 uppercase tracking-wider text-start">{{ __('global.order_summary') }}</h2>

                @if($hasActiveCoupons)
                {{-- Coupon Widget --}}
                <div class="mb-5">
                    <div class="flex gap-2">
                        <input type="text" x-model="couponCode"
                               @keydown.enter="applyCoupon(couponCode)"
                               placeholder="{{ __('global.coupon_enter_placeholder') }}"
                               aria-label="{{ __('global.coupon_enter_placeholder') }}"
                               class="flex-1 border border-slate-200/60 dark:border-slate-700/60 rounded-xl px-3 py-2.5 bg-white/60 dark:bg-slate-950/60 text-slate-800 dark:text-slate-200 text-sm font-semibold focus:ring-2 focus:ring-brand-primary focus:border-brand-primary dark:focus:border-accent outline-none transition-shadow placeholder:text-slate-400 backdrop-blur-sm">
                        <button type="button" @click="applyCoupon(couponCode)" :disabled="couponLoading"
                                class="px-4 py-2.5 bg-gradient-to-r from-brand-primary to-accent hover:from-brand-hover hover:to-accent-hover text-white rounded-xl text-xs font-extrabold transition-all shadow-sm hover:shadow cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!couponLoading">{{ __('global.coupon_apply_btn') }}</span>
                            <svg x-show="couponLoading" class="w-4 h-4 animate-spin mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </button>
                    </div>
                    <div class="mt-2" x-show="appliedCoupon" x-cloak>
                        <div class="flex items-center justify-between bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200/60 dark:border-emerald-800/40 rounded-xl px-3 py-2">
                            <span class="text-emerald-700 dark:text-emerald-400 font-bold text-xs" x-text="appliedCouponText"></span>
                            <button type="button" @click="removeCoupon()" :disabled="couponLoading"
                                    class="text-red-500 hover:text-red-700 text-[10px] font-extrabold px-2 py-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors uppercase tracking-wider cursor-pointer disabled:opacity-50">
                                <span x-show="!couponLoading">{{ __('global.coupon_remove_btn') }}</span>
                                <svg x-show="couponLoading" class="w-3 h-3 animate-spin mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="mt-1.5 text-xs font-semibold text-red-500" x-show="couponError" x-cloak x-text="couponError"></div>
                </div>
                @endif

                {{-- Line items --}}
                <div class="space-y-3.5 mb-5 text-sm">
                    <div class="flex justify-between text-slate-500 dark:text-slate-400">
                        <span class="font-semibold">{{ __('global.qty') }}</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300" x-text="items.reduce((acc, item) => acc + item.quantity, 0)"></span>
                    </div>
                    <div class="flex justify-between text-slate-500 dark:text-slate-400">
                        <span class="font-semibold">{{ __('global.shipping_cost') }}</span>
                        @if ($shipping === 0)
                        <span class="text-emerald-500 font-extrabold">{{ __('global.free') }}</span>
                        @elseif ($shipping > 0)
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $shipping }} {{ __('global.currency') }}</span>
                        @else
                        <span class="text-slate-400 font-semibold italic">{{ __('global.shipping_at_checkout') }}</span>
                        @endif
                    </div>
                </div>

                {{-- Total --}}
                <div class="flex justify-between items-baseline pt-4 border-t border-slate-200/40 dark:border-slate-800/60 mb-6">
                    <span class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">{{ __('global.total') }}</span>
                    <span class="text-2xl font-black text-brand-primary dark:text-accent tracking-tight" x-text="formatPrice(totalPrice)"></span>
                </div>

                {{-- Checkout CTA --}}
                <a href="{{ route('checkout') }}"
                   class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-brand-primary to-accent hover:from-brand-hover hover:to-accent-hover text-white font-extrabold py-3.5 rounded-xl shadow-[0_4px_20px_rgba(79,70,229,0.3)] hover:shadow-[0_8px_30px_rgba(79,70,229,0.5)] transition-all duration-300 hover:-translate-y-0.5 active:scale-95 group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">
                    {{ __('global.checkout') }}
                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1 rtl:group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        </aside>
    </div>
</main>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('cartView', (cartItems, csrfToken, initialTotal, shipping) => ({
                items: Object.values(cartItems),
                shipping: shipping,
                totalPrice: initialTotal + shipping,
                csrfToken: csrfToken,
                loadingItems: {},
                couponLoading: false,

            updateQty(variantId, newQty) {
                if (newQty <= 0) {
                    this.removeItem(variantId);
                    return;
                }

                this.loadingItems[variantId] = 'qty';

                const formData = new FormData();
                formData.append('_method', 'PATCH');
                formData.append('quantity', newQty);

                fetch(`/cart/${variantId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    const item = this.items.find(i => i.variant_id == variantId);
                    if (item) {
                        item.quantity = newQty;
                    }
                    this.totalPrice = Number(data.total) + this.shipping;
                    delete this.loadingItems[variantId];
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cartCount } }));
                })
                .catch(() => { delete this.loadingItems[variantId]; });
            },

            removeItem(variantId) {
                if (!confirm(@json(__('global.confirm_delete_cart')))) return;

                this.loadingItems[variantId] = 'remove';

                const formData = new FormData();
                formData.append('_method', 'DELETE');

                fetch(`/cart/${variantId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    this.items = this.items.filter(i => i.variant_id != variantId);
                    this.totalPrice = Number(data.total) + this.shipping;
                    delete this.loadingItems[variantId];
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cartCount } }));
                })
                .catch(() => { delete this.loadingItems[variantId]; });
            },

            isItemLoading(variantId) {
                return this.loadingItems[variantId];
            },

            formatPrice(price) {
                const locale = @json(app()->getLocale()) === 'ar' ? 'ar-EG' : 'en-EG';
                return new Intl.NumberFormat(locale, { style: 'currency', currency: 'EGP', maximumFractionDigits: 0 })
                    .format(Math.round(price));
            },
            couponCode: '',
            appliedCoupon: null,
            couponError: '',
            get appliedCouponText() {
                if (!this.appliedCoupon) return '';
                if (this.appliedCoupon.type === 'percent') return `${this.appliedCoupon.code} — {{ __('global.coupon_discount_label') }} ${this.appliedCoupon.value}%`;
                return `${this.appliedCoupon.code} — {{ __('global.coupon_discount_label') }} {{ __('global.currency') }}${this.appliedCoupon.value}`;
            },

            applyCoupon(code) {
                this.couponError = '';
                if (!code) return;
                this.couponLoading = true;
                fetch(@json(route('coupon.apply')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token())
                    },
                    body: JSON.stringify({ code })
                }).then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || @json(__('global.coupon_error')));
                    this.totalPrice = Number(data.total) + this.shipping;
                    this.appliedCoupon = data.coupon;
                    this.couponCode = '';
                    this.couponLoading = false;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message, type: 'success' } }));
                }).catch(err => {
                    this.couponError = err.message;
                    this.couponLoading = false;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: err.message, type: 'error' } }));
                });
            },

            removeCoupon() {
                this.couponError = '';
                this.couponLoading = true;
                fetch(@json(route('coupon.remove')), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': @json(csrf_token()) }
                }).then(res => res.json()).then(data => {
                    this.totalPrice = Number(data.total) + this.shipping;
                    this.appliedCoupon = null;
                    this.couponLoading = false;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message, type: 'success' } }));
                }).catch(() => { this.couponLoading = false; });
            }
        }));
    });
</script>
@endsection

