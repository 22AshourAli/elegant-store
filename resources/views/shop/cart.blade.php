@extends('layouts.store')

@section('content')
<div class="container mx-auto px-4 py-12" x-data="cartView({{ json_encode($cartItems) }}, '{{ csrf_token() }}', {{ $cart->total() }})">
    <h1 class="text-3xl font-extrabold mb-8 text-gray-900 dark:text-white">{{ __('global.shopping_cart') }}</h1>

    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Cart Items List (Left Side) -->
        <div class="lg:col-span-2 space-y-6">
            <template x-if="items.length === 0">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-12 text-center border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-gray-950 dark:text-gray-200">{{ __('global.empty_cart') }}</h3>
                    <a href="{{ route('home') }}" class="mt-6 inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">{{ __('global.home') }}</a>
                </div>
            </template>

            <template x-if="items.length > 0">
                <div class="space-y-4">
                    <template x-for="item in items" :key="item.variant_id">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 md:p-6 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            <!-- Product Image -->
                            <img :src="item.image || '/images/placeholder.jpg'" class="w-20 h-24 object-cover rounded-lg border border-gray-100 dark:border-gray-700 flex-shrink-0">

                            <!-- Product Details -->
                            <div class="flex-1 min-w-0 text-start">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white truncate" x-text="item.product_name"></h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    <span x-show="item.color">{{ __('global.color') }}: <span x-text="item.color"></span></span>
                                    <span x-show="item.color && item.size" class="mx-2">|</span>
                                    <span x-show="item.size">{{ __('global.size') }}: <span x-text="item.size"></span></span>
                                </p>
                                <p class="text-indigo-600 dark:text-indigo-400 font-bold mt-2" x-text="formatPrice(item.price)"></p>
                            </div>

                            <!-- Actions & Qty -->
                            <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto mt-4 sm:mt-0 pt-4 sm:pt-0 border-t sm:border-t-0 border-gray-100 dark:border-gray-700">
                                <!-- Quantity -->
                                <div class="flex items-center border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900">
                                    <button @click="updateQty(item.variant_id, item.quantity - 1)" class="px-3 py-1.5 text-gray-500 hover:text-indigo-600 focus:outline-none transition-colors">-</button>
                                    <input type="number" :value="item.quantity" class="w-10 text-center bg-transparent border-0 focus:ring-0 p-0 font-bold text-sm text-gray-900 dark:text-gray-100" readonly>
                                    <button @click="updateQty(item.variant_id, item.quantity + 1)" class="px-3 py-1.5 text-gray-500 hover:text-indigo-600 focus:outline-none transition-colors">+</button>
                                </div>

                                <!-- Subtotal -->
                                <div class="text-start sm:text-end min-w-[80px]">
                                    <p class="text-xs text-gray-400">{{ __('global.total') }}</p>
                                    <p class="font-bold text-gray-900 dark:text-white" x-text="formatPrice(item.price * item.quantity)"></p>
                                </div>

                                <!-- Remove button -->
                                <button @click="removeItem(item.variant_id)" class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors" title="{{ __('global.remove') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Summary Widget (Right Side) -->
        <div class="lg:col-span-1" x-show="items.length > 0">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm sticky top-24">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 pb-4 border-b dark:border-gray-700 text-start">{{ __('global.order_summary') }}</h2>

                <!-- Coupons -->
                <div class="mb-4">
                    <div class="flex gap-2">
                        <input type="text" x-model="couponCode" @keydown.enter="applyCoupon(couponCode)" placeholder="{{ __('global.coupon_enter_placeholder') }}" class="flex-1 border border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow">
                        <button type="button" @click="applyCoupon(couponCode)" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm hover:shadow">{{ __('global.coupon_apply_btn') }}</button>
                    </div>
                    <div class="mt-2" x-show="appliedCoupon" x-cloak>
                        <div class="flex items-center justify-between bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800/50 rounded-lg px-3 py-2">
                            <span class="text-green-700 dark:text-green-400 font-medium text-sm" x-text="appliedCouponText"></span>
                            <button type="button" @click="removeCoupon()" class="text-red-500 hover:text-red-700 text-xs font-semibold px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors">{{ __('global.coupon_remove_btn') }}</button>
                        </div>
                    </div>
                    <div class="mt-1 text-xs text-red-500" x-show="couponError" x-cloak x-text="couponError"></div>
                </div>

                <div class="space-y-4 mb-6">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>{{ __('global.qty') }}</span>
                        <span x-text="items.reduce((acc, item) => acc + item.quantity, 0)"></span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>{{ __('global.shipping_cost') }}</span>
                        @if ($shipping === 0)
                        <span class="text-green-500 font-semibold">{{ __('global.free') }}</span>
                        @elseif ($shipping > 0)
                        <span class="font-semibold">{{ $shipping }} {{ __('global.currency') }}</span>
                        @else
                        <span class="text-gray-400">{{ __('global.shipping_at_checkout') }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex justify-between items-baseline pt-4 border-t dark:border-gray-700 mb-8">
                    <span class="text-lg font-bold text-gray-900 dark:text-white">{{ __('global.total') }}</span>
                    <span class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400" x-text="formatPrice(totalPrice)"></span>
                </div>

                <a href="{{ route('checkout') }}" class="w-full block text-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-xl shadow-lg transition-all duration-300 transform hover:-translate-y-0.5">
                    {{ __('global.checkout') }} &rarr;
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('cartView', (cartItems, csrfToken, initialTotal) => ({
            items: Object.values(cartItems),
            totalPrice: initialTotal,
            csrfToken: csrfToken,

            updateQty(variantId, newQty) {
                if (newQty <= 0) {
                    this.removeItem(variantId);
                    return;
                }

                fetch(`/cart/${variantId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify({ quantity: newQty })
                })
                .then(res => res.json())
                .then(data => {
                    const item = this.items.find(i => i.variant_id == variantId);
                    if (item) {
                        item.quantity = newQty;
                    }
                    this.totalPrice = Number(data.total);
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cartCount } }));
                });
            },

            removeItem(variantId) {
                const confirmMsg = '{{ __('global.confirm_delete_cart') }}';
                if (!confirm(confirmMsg)) return;

                fetch(`/cart/${variantId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken
                    }
                })
                .then(res => res.json())
                .then(data => {
                    this.items = this.items.filter(i => i.variant_id != variantId);
                    this.totalPrice = Number(data.total);
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cartCount } }));
                });
            },

            formatPrice(price) {
                const locale = '{{ app()->getLocale() }}' === 'ar' ? 'ar-EG' : 'en-EG';
                return new Intl.NumberFormat(locale, { style: 'currency', currency: 'EGP', maximumFractionDigits: 0 })
                    .format(Math.round(price));
            }
            ,
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
                fetch('{{ route('coupon.apply') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ code })
                }).then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || '{{ __('global.coupon_error') }}');
                    this.totalPrice = Number(data.total);
                    this.appliedCoupon = data.coupon;
                    this.couponCode = '';
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message, type: 'success' } }));
                }).catch(err => {
                    this.couponError = err.message;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: err.message, type: 'error' } }));
                });
            },

            removeCoupon() {
                this.couponError = '';
                fetch('{{ route('coupon.remove') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).then(res => res.json()).then(data => {
                    this.totalPrice = Number(data.total);
                    this.appliedCoupon = null;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message, type: 'success' } }));
                });
            }
        }));
    });
</script>
@endsection

