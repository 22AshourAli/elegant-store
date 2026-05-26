@extends('layouts.store')

@section('content')
<div class="container mx-auto px-4 py-12">
    <h1 class="text-3xl font-extrabold mb-8 text-gray-900 dark:text-white">{{ __('global.checkout_title_page') }}</h1>

        @if(session('error'))
        <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/50 rounded-2xl p-4 mb-6 shadow-sm" x-data="{ show: true }" x-show="show">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-medium text-red-700 dark:text-red-400 flex-1">{{ session('error') }}</p>
                <button @click="show = false" class="text-red-400 hover:text-red-600 transition">&times;</button>
            </div>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-2xl p-4 mb-6 shadow-sm" x-data="{ show: true }" x-show="show">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-amber-700 dark:text-amber-400 mb-1">{{ __('global.validation_errors') }}</p>
                    <ul class="text-sm text-amber-600 dark:text-amber-300 list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button @click="show = false" class="text-amber-400 hover:text-amber-600 transition">&times;</button>
            </div>
        </div>
    @endif

    <form action="{{ route('checkout.store') }}" method="POST">
        @csrf
        <div class="grid lg:grid-cols-3 gap-8">

            <!-- Shipping Info Form (Left Side) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h2 class="text-xl font-bold mb-6 pb-2 border-b dark:border-gray-700 text-gray-900 dark:text-white">{{ __('global.shipping_info') }}</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('global.full_name') }} <span class="text-red-500">*</span></label>
                            <input type="text" value="{{ auth()->user()->name }}" readonly class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border bg-gray-50">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('global.phone_contact') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="phone" required value="{{ old('phone') }}" placeholder="{{ __('global.phone_example') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border @error('phone') border-red-500 dark:border-red-500 @enderror">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('global.shipping_address_full') }} <span class="text-red-500">*</span></label>
                            <textarea name="shipping_address" required rows="3" placeholder="{{ __('global.address_placeholder') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border @error('shipping_address') border-red-500 dark:border-red-500 @enderror">{{ old('shipping_address') }}</textarea>
                            @error('shipping_address')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('global.order_notes') }}</label>
                            <textarea name="notes" rows="2" placeholder="{{ __('global.order_notes_placeholder') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm" x-data="{ selectedMethod: 'cash' }">
                    <h2 class="text-xl font-bold mb-6 pb-2 border-b dark:border-gray-700 text-gray-900 dark:text-white">{{ __('global.payment_method_title') }}</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Cash on Delivery -->
                        <label class="relative flex flex-col items-center justify-center p-4 border-2 rounded-xl cursor-pointer transition-all duration-200"
                               :class="selectedMethod === 'cash' ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-950/20 dark:border-indigo-500 shadow-sm' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                            <input type="radio" name="payment_method" value="cash" x-model="selectedMethod" class="sr-only">
                            <svg class="w-8 h-8 text-gray-600 dark:text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span class="font-bold text-gray-900 dark:text-white text-sm">{{ __('global.cash_on_delivery_label') }}</span>
                        </label>

                        <!-- Credit Card -->
                        <label class="relative flex flex-col items-center justify-center p-4 border-2 rounded-xl cursor-pointer transition-all duration-200"
                               :class="selectedMethod === 'card' ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-950/20 dark:border-indigo-500 shadow-sm' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                            <input type="radio" name="payment_method" value="card" x-model="selectedMethod" class="sr-only">
                            <svg class="w-8 h-8 text-gray-600 dark:text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            <span class="font-bold text-gray-900 dark:text-white text-sm">{{ __('global.credit_card_label') }}</span>
                        </label>

                        <!-- Wallet -->
                        <label class="relative flex flex-col items-center justify-center p-4 border-2 rounded-xl cursor-pointer transition-all duration-200"
                               :class="selectedMethod === 'wallet' ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-950/20 dark:border-indigo-500 shadow-sm' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                            <input type="radio" name="payment_method" value="wallet" x-model="selectedMethod" class="sr-only">
                            <svg class="w-8 h-8 text-gray-600 dark:text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <span class="font-bold text-gray-900 dark:text-white text-sm">{{ __('global.wallet_label') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Summary Widget & Submit (Right Side) -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm sticky top-24">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 pb-4 border-b dark:border-gray-700">{{ __('global.your_order') }}</h2>

                    <div class="max-h-60 overflow-y-auto mb-6 divide-y dark:divide-gray-700">
                        @foreach($cartItems as $item)
                        <div class="flex justify-between py-3">
                            <div class="min-w-0 flex-1 ml-4 text-start">
                                <p class="font-bold text-sm text-gray-900 dark:text-white truncate">{{ $item['product_name'] }}</p>
                                <p class="text-xs text-gray-500">{{ __('global.qty_label') }} {{ $item['quantity'] }} | {{ $item['color'] }} / {{ $item['size'] }}</p>
                            </div>
                            <span class="font-bold text-sm text-gray-900 dark:text-white flex-shrink-0">{{ (int) round($item['price'] * $item['quantity']) }} {{ __('global.currency') }}</span>
                        </div>
                        @endforeach
                    </div>

                    <!-- Coupon Section -->
                    <div class="mb-4" x-data="checkoutCoupon()">
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

                    <div class="space-y-4 mb-6 pt-4 border-t dark:border-gray-700">
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>{{ __('global.products_total') }}</span>
                            <span>{{ (int) round($baseTotal) }} {{ __('global.currency') }}</span>
                        </div>
                        @if ($discount > 0 && $appliedCoupon)
                        <div class="flex justify-between text-green-600 dark:text-green-400">
                            <span>{{ __('global.coupon_discount_label') }} ({{ $appliedCoupon->code }})</span>
                            <span>-{{ (int) round($discount) }} {{ __('global.currency') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>{{ __('global.shipping_cost_label') }}</span>
                            @if ($shipping === 0)
                            <span class="text-green-500 font-semibold">{{ __('global.free') }}</span>
                            @else
                            <span class="font-semibold">{{ $shipping }} {{ __('global.currency') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-between items-baseline pt-4 border-t dark:border-gray-700 mb-8">
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ __('global.final_total') }}</span>
                        <span class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">{{ (int) round($finalTotal) }} {{ __('global.currency') }}</span>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-xl shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 flex justify-center items-center gap-2">
                        <span>{{ __('global.confirm_order') }}</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>

            @push('scripts')
            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('checkoutCoupon', () => ({
                        couponCode: '',
                        appliedCoupon: @json($appliedCoupon ? ['code' => $appliedCoupon->code, 'type' => $appliedCoupon->type, 'value' => $appliedCoupon->value] : null),
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
                                this.appliedCoupon = data.coupon;
                                this.couponCode = '';
                                window.location.reload();
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
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    }));
                });
            </script>
            @endpush
        </div>
    </form>
</div>
@endsection

