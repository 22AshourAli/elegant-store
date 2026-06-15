@extends('layouts.store')

@section('content')
<div class="container mx-auto px-4 py-12" x-data="checkoutPage({
    baseTotal: {{ (int) round($baseTotal) }},
    discount: {{ (int) round($discount) }},
    shipping: {{ (int) round($shipping) }},
    finalTotal: {{ (int) round($finalTotal) }},
    appliedCoupon: @json($appliedCoupon ? ['code' => $appliedCoupon->code, 'type' => $appliedCoupon->type, 'value' => $appliedCoupon->value] : null),
    governorates: @json($governorates ?? []),
    shippingCostUrl: '{{ route('api.shipping.calculate') }}',
    citiesUrl: '{{ route('api.shipping.cities') }}'
})">
    <h1 class="text-3xl font-extrabold mb-8 text-slate-900 dark:text-white">{{ __('global.checkout_title_page') }}</h1>

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

    <form action="{{ route('checkout.store') }}" method="POST" @submit="submitting = true">
        @csrf
        <div class="grid lg:grid-cols-3 gap-8">

            <!-- Shipping Info Form (Left Side) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Contact Info -->
                <div class="bg-white/70 dark:bg-surface-dark/60 rounded-2xl p-6 border border-slate-200/40 dark:border-slate-800/40 shadow-sm backdrop-blur-sm">
                    <h2 class="text-xl font-bold mb-6 pb-2 border-b border-slate-200/40 dark:border-slate-800/60 text-slate-900 dark:text-white">{{ __('global.shipping_info') }}</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.full_name') }} <span class="text-red-500">*</span></label>
                            <input type="text" value="{{ auth()->user()->name }}" readonly class="w-full border border-slate-200/60 dark:border-slate-700/60 rounded-xl shadow-sm bg-slate-100/60 dark:bg-slate-800/40 text-slate-700 dark:text-slate-300 px-4 py-3 text-sm font-semibold cursor-not-allowed opacity-70">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.phone_contact') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="phone" required value="{{ old('phone') }}" placeholder="{{ __('global.phone_example') }}" class="w-full border border-slate-200/60 dark:border-slate-700/60 rounded-xl shadow-sm focus:border-brand-primary dark:focus:border-accent focus:ring-2 focus:ring-brand-primary/20 dark:focus:ring-accent/20 bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white px-4 py-3 text-sm font-semibold outline-none transition-all @error('phone') border-red-500 dark:border-red-500 @enderror">
                            @error('phone') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <!-- Governorate -->
                        <div>
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.governorate') }} <span class="text-red-500">*</span></label>
                            <select name="governorate_id" required x-model="governorateId" @change="onGovernorateChange" class="w-full border border-slate-200/60 dark:border-slate-700/60 rounded-xl shadow-sm focus:border-brand-primary dark:focus:border-accent focus:ring-2 focus:ring-brand-primary/20 dark:focus:ring-accent/20 bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white px-4 py-3 text-sm font-semibold outline-none transition-all">
                                <option value="">{{ __('global.select_governorate') }}</option>
                                <template x-for="gov in governorates" :key="gov.id">
                                    <option x-bind:value="gov.id" x-text="gov.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- City -->
                        <div>
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.admin_cities') }} <span class="text-red-500">*</span></label>
                            <select name="city_id" required x-model="cityId" @change="onCityChange" class="w-full border border-slate-200/60 dark:border-slate-700/60 rounded-xl shadow-sm focus:border-brand-primary dark:focus:border-accent focus:ring-2 focus:ring-brand-primary/20 dark:focus:ring-accent/20 bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white px-4 py-3 text-sm font-semibold outline-none transition-all">
                                <option value="">{{ __('global.select_city') }}</option>
                                <template x-for="city in cities" :key="city.id">
                                    <option x-bind:value="city.id" x-text="city.name"></option>
                                </template>
                            </select>
                            <p x-show="citiesLoading" class="mt-1 text-xs text-slate-500" x-text="'{{ __('global.loading_cities') }}'"></p>
                        </div>

                        <!-- Detailed Address -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.address_street') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="address_street" required value="{{ old('address_street') }}" class="w-full border border-slate-200/60 dark:border-slate-700/60 rounded-xl shadow-sm focus:border-brand-primary dark:focus:border-accent focus:ring-2 focus:ring-brand-primary/20 dark:focus:ring-accent/20 bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white px-4 py-3 text-sm font-semibold outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.address_building') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="address_building" required value="{{ old('address_building') }}" class="w-full border border-slate-200/60 dark:border-slate-700/60 rounded-xl shadow-sm focus:border-brand-primary dark:focus:border-accent focus:ring-2 focus:ring-brand-primary/20 dark:focus:ring-accent/20 bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white px-4 py-3 text-sm font-semibold outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.address_floor') }}</label>
                                <input type="text" name="address_floor" value="{{ old('address_floor') }}" class="w-full border border-slate-200/60 dark:border-slate-700/60 rounded-xl shadow-sm focus:border-brand-primary dark:focus:border-accent focus:ring-2 focus:ring-brand-primary/20 dark:focus:ring-accent/20 bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white px-4 py-3 text-sm font-semibold outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.address_apartment') }}</label>
                                <input type="text" name="address_apartment" value="{{ old('address_apartment') }}" class="w-full border border-slate-200/60 dark:border-slate-700/60 rounded-xl shadow-sm focus:border-brand-primary dark:focus:border-accent focus:ring-2 focus:ring-brand-primary/20 dark:focus:ring-accent/20 bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white px-4 py-3 text-sm font-semibold outline-none transition-all">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.address_landmark') }}</label>
                            <input type="text" name="address_landmark" value="{{ old('address_landmark') }}" class="w-full border border-slate-200/60 dark:border-slate-700/60 rounded-xl shadow-sm focus:border-brand-primary dark:focus:border-accent focus:ring-2 focus:ring-brand-primary/20 dark:focus:ring-accent/20 bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white px-4 py-3 text-sm font-semibold outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.address_type') }}</label>
                            <select name="address_type" class="w-full border border-slate-200/60 dark:border-slate-700/60 rounded-xl shadow-sm focus:border-brand-primary dark:focus:border-accent focus:ring-2 focus:ring-brand-primary/20 dark:focus:ring-accent/20 bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white px-4 py-3 text-sm font-semibold outline-none transition-all">
                                <option value="home" {{ old('address_type', 'home') === 'home' ? 'selected' : '' }}>{{ __('global.address_type_home') }}</option>
                                <option value="work" {{ old('address_type') === 'work' ? 'selected' : '' }}>{{ __('global.address_type_work') }}</option>
                            </select>
                        </div>

                        <!-- Legacy shipping_address hidden field for backward compatibility -->
                        <div class="hidden">
                            <textarea name="shipping_address" x-model="compositeAddress"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.order_notes') }}</label>
                            <textarea name="notes" rows="2" placeholder="{{ __('global.order_notes_placeholder') }}" class="w-full border border-slate-200/60 dark:border-slate-700/60 rounded-xl shadow-sm focus:border-brand-primary dark:focus:border-accent focus:ring-2 focus:ring-brand-primary/20 dark:focus:ring-accent/20 bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white px-4 py-3 text-sm font-semibold outline-none transition-all">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="bg-white/70 dark:bg-surface-dark/60 rounded-2xl p-6 border border-slate-200/40 dark:border-slate-800/40 shadow-sm backdrop-blur-sm @error('payment_method') !border-red-500 dark:!border-red-500 @enderror" x-data="{ selectedMethod: 'cash' }">
                    <h2 class="text-xl font-bold mb-6 pb-2 border-b border-slate-200/40 dark:border-slate-800/60 text-slate-900 dark:text-white">{{ __('global.payment_method_title') }}</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="payment-method-group" role="radiogroup" aria-label="{{ __('global.payment_method_title') }}">
                        <label class="relative flex flex-col items-center justify-center p-5 border-2 rounded-xl cursor-pointer transition-all duration-300"
                               :class="selectedMethod === 'cash' ? 'border-brand-primary bg-brand-primary/5 dark:bg-accent/10 dark:border-accent shadow-[0_0_25px_rgba(79,70,229,0.15)] scale-[1.02]' : 'border-slate-200/60 dark:border-slate-800/60 hover:border-brand-primary/40 dark:hover:border-accent/40 hover:shadow-sm bg-white/40 dark:bg-slate-900/30'">
                            <input type="radio" name="payment_method" value="cash" x-model="selectedMethod" class="sr-only">
                            <svg class="w-8 h-8 mb-2 transition-colors duration-300" :class="selectedMethod === 'cash' ? 'text-brand-primary dark:text-accent' : 'text-slate-400 dark:text-slate-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span class="font-bold text-sm transition-colors duration-300" :class="selectedMethod === 'cash' ? 'text-brand-primary dark:text-accent' : 'text-slate-700 dark:text-slate-300'">{{ __('global.cash_on_delivery_label') }}</span>
                            <template x-if="selectedMethod === 'cash'">
                                <span class="absolute -top-2 -end-2 w-5 h-5 bg-brand-primary dark:bg-accent rounded-full flex items-center justify-center shadow-md animate-scaleIn">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"></path></svg>
                                </span>
                            </template>
                        </label>

                        <label class="relative flex flex-col items-center justify-center p-5 border-2 rounded-xl cursor-pointer transition-all duration-300"
                               :class="selectedMethod === 'card' ? 'border-brand-primary bg-brand-primary/5 dark:bg-accent/10 dark:border-accent shadow-[0_0_25px_rgba(79,70,229,0.15)] scale-[1.02]' : 'border-slate-200/60 dark:border-slate-800/60 hover:border-brand-primary/40 dark:hover:border-accent/40 hover:shadow-sm bg-white/40 dark:bg-slate-900/30'">
                            <input type="radio" name="payment_method" value="card" x-model="selectedMethod" class="sr-only">
                            <svg class="w-8 h-8 mb-2 transition-colors duration-300" :class="selectedMethod === 'card' ? 'text-brand-primary dark:text-accent' : 'text-slate-400 dark:text-slate-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            <span class="font-bold text-sm transition-colors duration-300" :class="selectedMethod === 'card' ? 'text-brand-primary dark:text-accent' : 'text-slate-700 dark:text-slate-300'">{{ __('global.credit_card_label') }}</span>
                            <template x-if="selectedMethod === 'card'">
                                <span class="absolute -top-2 -end-2 w-5 h-5 bg-brand-primary dark:bg-accent rounded-full flex items-center justify-center shadow-md animate-scaleIn">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"></path></svg>
                                </span>
                            </template>
                        </label>

                        <label class="relative flex flex-col items-center justify-center p-5 border-2 rounded-xl cursor-pointer transition-all duration-300"
                               :class="selectedMethod === 'wallet' ? 'border-brand-primary bg-brand-primary/5 dark:bg-accent/10 dark:border-accent shadow-[0_0_25px_rgba(79,70,229,0.15)] scale-[1.02]' : 'border-slate-200/60 dark:border-slate-800/60 hover:border-brand-primary/40 dark:hover:border-accent/40 hover:shadow-sm bg-white/40 dark:bg-slate-900/30'">
                            <input type="radio" name="payment_method" value="wallet" x-model="selectedMethod" class="sr-only">
                            <svg class="w-8 h-8 mb-2 transition-colors duration-300" :class="selectedMethod === 'wallet' ? 'text-brand-primary dark:text-accent' : 'text-slate-400 dark:text-slate-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <span class="font-bold text-sm transition-colors duration-300" :class="selectedMethod === 'wallet' ? 'text-brand-primary dark:text-accent' : 'text-slate-700 dark:text-slate-300'">{{ __('global.wallet_label') }}</span>
                            <template x-if="selectedMethod === 'wallet'">
                                <span class="absolute -top-2 -end-2 w-5 h-5 bg-brand-primary dark:bg-accent rounded-full flex items-center justify-center shadow-md animate-scaleIn">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"></path></svg>
                                </span>
                            </template>
                        </label>
                    </div>
                    @error('payment_method') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Summary Widget & Submit (Right Side) -->
            <div class="lg:col-span-1">
                <div class="glass-premium rounded-2xl p-6 border border-slate-200/40 dark:border-slate-800/40 shadow-[0_8px_32px_0_rgba(31,38,135,0.06)] dark:shadow-[0_8px_32px_0_rgba(0,0,0,0.4)] sticky top-24">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6 pb-4 border-b border-slate-200/40 dark:border-slate-800/60">{{ __('global.your_order') }}</h2>

                    <div class="max-h-60 overflow-y-auto mb-6 divide-y divide-slate-200/40 dark:divide-slate-800/60 no-scrollbar">
                        @foreach($cartItems as $item)
                        <div class="flex justify-between py-3 gap-2">
                            <div class="min-w-0 flex-1 text-start">
                                <p class="font-bold text-sm text-slate-900 dark:text-white break-words leading-snug">{{ $item['product_name'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 break-words">{{ __('global.qty_label') }} {{ $item['quantity'] }} | {{ $item['color'] }} / {{ $item['size'] }}</p>
                            </div>
                            <span class="font-extrabold text-sm text-slate-900 dark:text-white flex-shrink-0 whitespace-nowrap">{{ (int) round($item['price'] * $item['quantity']) }} {{ __('global.currency') }}</span>
                        </div>
                        @endforeach
                    </div>

                    @if($hasActiveCoupons)
                    <div class="mb-4">
                        <div class="flex gap-2">
                            <input type="text" x-model="couponCode" @keydown.enter="applyCoupon(couponCode)" placeholder="{{ __('global.coupon_enter_placeholder') }}" class="flex-1 border border-slate-200/60 dark:border-slate-700/60 rounded-xl px-3 py-2.5 bg-white/60 dark:bg-slate-900/60 text-slate-900 dark:text-white text-sm font-semibold focus:ring-2 focus:ring-brand-primary focus:border-brand-primary dark:focus:border-accent outline-none transition-shadow placeholder:text-slate-400 backdrop-blur-sm">
                            <button type="button" @click="applyCoupon(couponCode)" :disabled="couponLoading" class="px-4 py-2.5 bg-gradient-to-r from-brand-primary to-accent hover:from-brand-hover hover:to-accent-hover text-white rounded-xl text-sm font-extrabold transition-all shadow-sm hover:shadow cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!couponLoading">{{ __('global.coupon_apply_btn') }}</span>
                                <svg x-show="couponLoading" class="w-4 h-4 animate-spin mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </button>
                        </div>
                        <div class="mt-2" x-show="appliedCoupon" x-cloak>
                            <div class="flex items-center justify-between bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200/60 dark:border-emerald-800/40 rounded-xl px-3 py-2">
                                <span class="text-emerald-700 dark:text-emerald-400 font-bold text-sm" x-text="appliedCouponText"></span>
                                <button type="button" @click="removeCoupon()" :disabled="couponLoading" class="text-red-500 hover:text-red-700 text-xs font-extrabold px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors cursor-pointer disabled:opacity-50">{{ __('global.coupon_remove_btn') }}</button>
                            </div>
                        </div>
                        <div class="mt-1 text-xs text-red-500 font-semibold" x-show="couponError" x-cloak x-text="couponError"></div>
                    </div>
                    @endif

                    <div class="space-y-4 mb-6 pt-4 border-t border-slate-200/40 dark:border-slate-800/60">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400 text-sm">
                            <span class="font-semibold">{{ __('global.products_total') }}</span>
                            <span class="font-bold" x-text="formatPrice(baseTotal)"></span>
                        </div>
                        <div class="flex justify-between text-emerald-600 dark:text-emerald-400 text-sm" x-show="discount > 0 && appliedCoupon" x-cloak>
                            <span class="font-semibold">{{ __('global.coupon_discount_label') }} (<span x-text="appliedCoupon?.code"></span>)</span>
                            <span class="font-bold" x-text="'-' + formatPrice(discount)"></span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-400 text-sm">
                            <span class="font-semibold">{{ __('global.shipping_cost_label') }}</span>
                            <template x-if="shippingCalculating">
                                <span class="text-slate-400 italic text-xs">{{ __('global.shipping_calculating') }}</span>
                            </template>
                            <template x-if="!shippingCalculating && shipping === 0">
                                <span class="text-emerald-500 font-extrabold">{{ __('global.free') }}</span>
                            </template>
                            <template x-if="!shippingCalculating && shipping > 0">
                                <span class="font-bold" x-text="formatPrice(shipping)"></span>
                            </template>
                        </div>
                    </div>

                    <div class="flex justify-between items-baseline pt-4 border-t border-slate-200/40 dark:border-slate-800/60 mb-8">
                        <span class="text-lg font-extrabold text-slate-900 dark:text-white">{{ __('global.final_total') }}</span>
                        <span class="text-2xl font-black text-brand-primary dark:text-accent" x-text="formatPrice(finalTotal)"></span>
                    </div>

                    <button type="submit" :disabled="submitting || shippingCalculating" class="w-full bg-gradient-to-r from-brand-primary to-accent hover:from-brand-hover hover:to-accent-hover text-white font-extrabold py-4 rounded-xl shadow-[0_4px_20px_rgba(79,70,229,0.3)] hover:shadow-[0_8px_30px_rgba(79,70,229,0.5)] transition-all duration-300 hover:-translate-y-0.5 active:scale-[0.97] flex justify-center items-center gap-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                        <span x-show="!submitting">{{ __('global.confirm_order') }}</span>
                        <svg x-show="!submitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        <svg x-show="submitting" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-show="submitting" x-text="'{{ __('global.processing') }}...'"></span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('checkoutPage', (initial) => ({
            baseTotal: initial.baseTotal,
            discount: initial.discount,
            shipping: initial.shipping,
            finalTotal: initial.finalTotal,
            appliedCoupon: initial.appliedCoupon,
            couponCode: '',
            couponError: '',
            couponLoading: false,
            submitting: false,
            governorates: initial.governorates || [],
            governorateId: '',
            cityId: '',
            cities: [],
            citiesLoading: false,
            shippingCalculating: false,
            shippingCostUrl: initial.shippingCostUrl,
            citiesUrl: initial.citiesUrl,

            get compositeAddress() {
                const gov = this.governorates.find(g => g.id == this.governorateId);
                const city = this.cities.find(c => c.id == this.cityId);
                const parts = [];
                if (gov) parts.push(gov.name);
                if (city) parts.push(city.name);
                const els = [];
                const street = document.querySelector('[name="address_street"]')?.value;
                const building = document.querySelector('[name="address_building"]')?.value;
                const floor = document.querySelector('[name="address_floor"]')?.value;
                const apt = document.querySelector('[name="address_apartment"]')?.value;
                if (street) els.push(street);
                if (building) els.push(building);
                if (floor) els.push(`Floor ${floor}`);
                if (apt) els.push(`Apt ${apt}`);
                if (els.length) parts.push(els.join(', '));
                return parts.join(' - ') || '';
            },

            get appliedCouponText() {
                if (!this.appliedCoupon) return '';
                if (this.appliedCoupon.type === 'percent') return `${this.appliedCoupon.code} — {{ __('global.coupon_discount_label') }} ${this.appliedCoupon.value}%`;
                return `${this.appliedCoupon.code} — {{ __('global.coupon_discount_label') }} {{ __('global.currency') }}${this.appliedCoupon.value}`;
            },

            formatPrice(price) {
                const locale = @json(app()->getLocale()) === 'ar' ? 'ar-EG' : 'en-EG';
                const value = Math.round(parseFloat(price || 0));
                return new Intl.NumberFormat(locale, { style: 'currency', currency: 'EGP', maximumFractionDigits: 0 }).format(value);
            },

            async onGovernorateChange() {
                this.cityId = '';
                this.cities = [];
                this.shipping = 0;
                this.finalTotal = this.baseTotal - this.discount;
                if (!this.governorateId) return;
                this.citiesLoading = true;
                try {
                    const res = await fetch(`${this.citiesUrl}?governorate_id=${this.governorateId}`);
                    this.cities = await res.json();
                } catch(e) {}
                this.citiesLoading = false;
            },

            async onCityChange() {
                if (!this.governorateId) return;
                this.shippingCalculating = true;
                try {
                    const res = await fetch(this.shippingCostUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({
                            governorate_id: this.governorateId,
                            city_id: this.cityId || null,
                            cart_total: this.baseTotal - this.discount,
                        })
                    });
                    const data = await res.json();
                    this.shipping = data.final_cost || 0;
                    this.finalTotal = (this.baseTotal - this.discount) + this.shipping;
                } catch(e) {}
                this.shippingCalculating = false;
            },

            applyCoupon(code) {
                this.couponError = '';
                if (!code) return;
                this.couponLoading = true;
                fetch('{{ route('coupon.apply') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ code })
                }).then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || '{{ __('global.coupon_error') }}');
                    this.appliedCoupon = data.coupon;
                    this.baseTotal = Number(data.baseTotal);
                    this.discount = Number(data.discount);
                    this.finalTotal = Number(data.total) + this.shipping;
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
                fetch('{{ route('coupon.remove') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).then(async res => {
                    const data = await res.json();
                    this.appliedCoupon = null;
                    this.baseTotal = Number(data.baseTotal);
                    this.discount = Number(data.discount);
                    this.finalTotal = Number(data.total) + this.shipping;
                    this.couponLoading = false;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message, type: 'success' } }));
                }).catch(() => { this.couponLoading = false; });
            }
        }));
    });
</script>
@endpush
