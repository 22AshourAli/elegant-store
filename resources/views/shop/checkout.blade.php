@extends('layouts.store')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50/30 dark:from-gray-950 dark:via-gray-900 dark:to-indigo-950/20 py-6 sm:py-10" x-data="checkoutPage({
    baseTotal: {{ (int) round($baseTotal) }},
    discount: {{ (int) round($discount) }},
    shipping: {{ (int) round($shipping) }},
    finalTotal: {{ (int) round($finalTotal) }},
    appliedCoupon: @json($appliedCoupon ? ['code' => $appliedCoupon->code, 'type' => $appliedCoupon->type, 'value' => $appliedCoupon->value] : null),
})">
    <div class="max-w-6xl mx-auto px-3 sm:px-4 lg:px-6">
        <!-- Header -->
        <div class="flex items-center gap-3 mb-6 sm:mb-8">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-200 dark:shadow-indigo-900/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('global.checkout_title_page') }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('global.checkout_subtitle') }}</p>
            </div>
        </div>

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
            <div class="grid lg:grid-cols-5 gap-4 sm:gap-6 lg:gap-8">

                <!-- ===== LEFT: Form (3/5) ===== -->
                <div class="lg:col-span-3 space-y-4 sm:space-y-6">

                    <!-- Contact -->
                    <div class="bg-white dark:bg-gray-800/80 rounded-2xl p-4 sm:p-6 border border-slate-200/60 dark:border-slate-700/60 shadow-sm backdrop-blur-sm">
                        <div class="flex items-center gap-2.5 mb-5 pb-3 border-b border-slate-100 dark:border-slate-700/60">
                            <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">{{ __('global.contact_info') }}</h2>
                                <p class="text-xs text-slate-400 dark:text-slate-500">{{ auth()->user()->name }} &middot; {{ auth()->user()->email }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.phone_contact') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="phone" required value="{{ old('phone') }}" placeholder="{{ __('global.phone_example') }}"
                                class="w-full border border-slate-200/60 dark:border-slate-700/60 rounded-xl shadow-sm focus:border-indigo-400 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800/40 bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white px-4 py-3 text-sm font-semibold outline-none transition-all @error('phone') border-red-400 dark:border-red-500 @enderror">
                            @error('phone') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="bg-white dark:bg-gray-800/80 rounded-2xl p-4 sm:p-6 border border-slate-200/60 dark:border-slate-700/60 shadow-sm backdrop-blur-sm">
                        <div class="flex items-center gap-2.5 mb-5 pb-3 border-b border-slate-100 dark:border-slate-700/60">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">{{ __('global.shipping_info') }}</h2>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3 sm:gap-4">
                            <!-- Governorate -->
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.governorate') }} <span class="text-red-500">*</span></label>
                                <select name="governorate_id" id="governorate_select" required x-model="governorateId" @change="onGovernorateChange"
                                    class="w-full border border-slate-200/60 dark:border-slate-700/60 rounded-xl shadow-sm focus:border-indigo-400 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800/40 bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white px-4 py-3 text-sm font-semibold outline-none transition-all appearance-none bg-[length:16px] bg-[right_12px_center] bg-no-repeat dark:bg-[right_12px_center]"
                                    style="background-image: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\")">
                                    <option value="">{{ __('global.select_governorate') }}</option>
                                    @forelse($governorates as $gov)
                                    <option value="{{ $gov['id'] }}" {{ old('governorate_id') == $gov['id'] ? 'selected' : '' }}>{{ $gov['name'] }}</option>
                                    @empty
                                    <option value="" disabled>⚠️ {{ __('global.no_governorates') }}</option>
                                    @endforelse
                                </select>
                            </div>

                            <!-- City -->
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.city') }} <span class="text-red-500">*</span></label>
                                <select name="city_id" id="city_select" required x-model="cityId" @change="onCityChange" {{ old('governorate_id') ? '' : 'disabled' }}
                                    class="w-full border border-slate-200/60 dark:border-slate-700/60 rounded-xl shadow-sm focus:border-indigo-400 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800/40 bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white px-4 py-3 text-sm font-semibold outline-none transition-all appearance-none bg-[length:16px] bg-[right_12px_center] bg-no-repeat dark:bg-[right_12px_center] disabled:opacity-50 disabled:cursor-not-allowed"
                                    style="background-image: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\")">
                                    <option value="">{{ __('global.select_city') }}</option>
                                    @foreach($governorates as $gov)
                                    @foreach($gov['cities'] ?? [] as $city)
                                    <option value="{{ $city['id'] }}" data-gov="{{ $gov['id'] }}" style="display:none" {{ old('city_id') == $city['id'] ? 'selected' : '' }}>{{ $city['name'] }}</option>
                                    @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="mt-3 sm:mt-4">
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.shipping_address_full') }} <span class="text-red-500">*</span></label>
            <textarea name="shipping_address" required rows="2" placeholder="{{ __('global.address_placeholder') }}"
                x-model="shippingAddress"
                @input="addressAutoFilled = false"
                class="w-full border border-slate-200/60 dark:border-slate-700/60 rounded-xl shadow-sm focus:border-indigo-400 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800/40 bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white px-4 py-3 text-sm font-semibold outline-none transition-all resize-none @error('shipping_address') border-red-400 dark:border-red-500 @enderror"></textarea>
                            @error('shipping_address') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mt-3 sm:mt-4">
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.order_notes') }}</label>
                            <textarea name="notes" rows="1" placeholder="{{ __('global.order_notes_placeholder') }}"
                                class="w-full border border-slate-200/60 dark:border-slate-700/60 rounded-xl shadow-sm focus:border-indigo-400 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800/40 bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white px-4 py-3 text-sm font-semibold outline-none transition-all resize-none">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-white dark:bg-gray-800/80 rounded-2xl p-4 sm:p-6 border border-slate-200/60 dark:border-slate-700/60 shadow-sm backdrop-blur-sm">
                        <div class="flex items-center gap-2.5 mb-5 pb-3 border-b border-slate-100 dark:border-slate-700/60">
                            <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">{{ __('global.payment_method_title') }}</h2>
                        </div>

                        <div class="grid grid-cols-3 gap-2 sm:gap-3" x-data="{ selectedMethod: '{{ old('payment_method', 'cash') }}' }">
                            <template x-for="(pm, key) in {cash: '{{ __('global.cash_on_delivery_label') }}', card: '{{ __('global.credit_card_label') }}', wallet: '{{ __('global.wallet_label') }}'}" :key="key">
                                <label class="relative flex flex-col items-center justify-center p-3 sm:p-4 border-2 rounded-xl cursor-pointer transition-all duration-200"
                                    :class="selectedMethod === key ? 'border-indigo-500 bg-indigo-50/60 dark:border-indigo-400 dark:bg-indigo-950/30 shadow-md' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-white/50 dark:bg-slate-900/40'">
                                    <input type="radio" name="payment_method" x-bind:value="key" x-model="selectedMethod" class="sr-only">
                                    <template x-if="key === 'cash'">
                                        <svg class="w-6 h-6 sm:w-7 sm:h-7 mb-1.5 transition-colors" :class="selectedMethod === 'cash' ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </template>
                                    <template x-if="key === 'card'">
                                        <svg class="w-6 h-6 sm:w-7 sm:h-7 mb-1.5 transition-colors" :class="selectedMethod === 'card' ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    </template>
                                    <template x-if="key === 'wallet'">
                                        <svg class="w-6 h-6 sm:w-7 sm:h-7 mb-1.5 transition-colors" :class="selectedMethod === 'wallet' ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    </template>
                                    <span class="text-[10px] sm:text-xs font-bold text-center leading-tight transition-colors" :class="selectedMethod === key ? 'text-indigo-700 dark:text-indigo-300' : 'text-slate-500 dark:text-slate-400'" x-text="pm"></span>
                                    <template x-if="selectedMethod === key">
                                        <span class="absolute -top-1.5 -end-1.5 w-4 h-4 bg-indigo-500 dark:bg-indigo-400 rounded-full flex items-center justify-center shadow-sm">
                                            <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                    </template>
                                </label>
                            </template>
                        </div>
                        @error('payment_method') <p class="mt-2 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- ===== RIGHT: Summary (2/5) ===== -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800/80 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-lg shadow-slate-200/50 dark:shadow-slate-900/30 sticky top-24 overflow-hidden backdrop-blur-sm">
                        <!-- Summary header -->
                        <div class="p-4 sm:p-6 border-b border-slate-100 dark:border-slate-700/60">
                            <div class="flex items-center justify-between">
                                <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    {{ __('global.your_order') }}
                                </h2>
                                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-lg">{{ count($cartItems) }} {{ __('global.items_count') }}</span>
                            </div>
                        </div>

                        <!-- Products -->
                        <div class="max-h-48 sm:max-h-60 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/60 no-scrollbar">
                            @foreach($cartItems as $item)
                            <div class="flex items-center gap-3 p-3 sm:p-4 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <img src="{{ $item['image'] ?? asset('images/logo.svg') }}" alt="{{ $item['product_name'] }}" loading="lazy"
                                    class="w-10 h-12 sm:w-12 sm:h-14 rounded-xl object-cover flex-shrink-0 border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800">
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-sm text-slate-900 dark:text-white truncate">{{ $item['product_name'] }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        @if($item['color']){{ $item['color'] }}@endif @if($item['color'] && $item['size'])/@endif @if($item['size']){{ $item['size'] }}@endif
                                    </p>
                                </div>
                                <span class="font-extrabold text-sm text-slate-900 dark:text-white flex-shrink-0">{{ (int) round($item['price'] * $item['quantity']) }} {{ __('global.currency') }}</span>
                            </div>
                            @endforeach
                        </div>

                        <!-- Coupon -->
                        @if($hasActiveCoupons)
                        <div class="px-4 sm:px-6 py-3 border-t border-slate-100 dark:border-slate-700/60">
                            <div class="flex gap-2">
                                <input type="text" x-model="couponCode" @keydown.enter="applyCoupon(couponCode)"
                                    placeholder="{{ __('global.coupon_enter_placeholder') }}"
                                    class="flex-1 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 bg-white/60 dark:bg-slate-900/60 text-slate-900 dark:text-white text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-300 dark:focus:ring-indigo-700 focus:border-indigo-400 outline-none transition-shadow placeholder:text-slate-400">
                                <button type="button" @click="applyCoupon(couponCode)" :disabled="couponLoading"
                                    class="px-3 sm:px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white rounded-xl text-xs sm:text-sm font-extrabold transition-all shadow-sm hover:shadow cursor-pointer flex items-center gap-1.5 disabled:cursor-not-allowed">
                                    <span x-show="!couponLoading">{{ __('global.coupon_apply_btn') }}</span>
                                    <svg x-show="couponLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                                </button>
                            </div>
                            <div x-show="appliedCoupon" x-cloak class="mt-2">
                                <div class="flex items-center justify-between bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200/60 dark:border-emerald-800/40 rounded-xl px-3 py-2">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="text-emerald-700 dark:text-emerald-400 font-bold text-xs sm:text-sm" x-text="appliedCouponText"></span>
                                    </div>
                                    <button type="button" @click="removeCoupon()" :disabled="couponLoading"
                                        class="text-red-500 hover:text-red-700 text-xs font-extrabold px-2 py-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors cursor-pointer disabled:opacity-50">{{ __('global.coupon_remove_btn') }}</button>
                                </div>
                            </div>
                            <div x-show="couponError" x-cloak class="mt-1 text-xs text-red-500 font-semibold flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span x-text="couponError"></span>
                            </div>
                        </div>
                        @endif

                        <!-- Totals -->
                        <div class="px-4 sm:px-6 py-4 space-y-3 border-t border-slate-100 dark:border-slate-700/60">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500 dark:text-slate-400">{{ __('global.products_total') }}</span>
                                <span class="font-bold text-slate-900 dark:text-white" x-text="formatPrice(baseTotal)"></span>
                            </div>
                            <div class="flex justify-between text-sm" x-show="discount > 0 && appliedCoupon" x-cloak>
                                <span class="text-emerald-600 dark:text-emerald-400">{{ __('global.coupon_discount_label') }}</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="'-' + formatPrice(discount)"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500 dark:text-slate-400">{{ __('global.shipping_cost_label') }}</span>
                                <span>
                                    <template x-if="shippingCalculating">
                                        <span class="text-slate-400 text-xs italic">{{ __('global.shipping_calculating') }}</span>
                                    </template>
                                    <template x-if="!shippingCalculating && shipping === 0">
                                        <span class="text-emerald-500 font-extrabold text-xs">{{ __('global.free') }}</span>
                                    </template>
                                    <template x-if="!shippingCalculating && shipping > 0">
                                        <span class="font-bold text-slate-900 dark:text-white" x-text="formatPrice(shipping)"></span>
                                    </template>
                                </span>
                            </div>
                        </div>

                        <!-- Final Total -->
                        <div class="px-4 sm:px-6 py-4 bg-gradient-to-r from-indigo-50/80 to-purple-50/80 dark:from-indigo-950/30 dark:to-purple-950/30 border-t border-slate-100 dark:border-slate-700/60">
                            <div class="flex justify-between items-baseline">
                                <span class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white">{{ __('global.final_total') }}</span>
                                <span class="text-xl sm:text-2xl font-black text-indigo-600 dark:text-indigo-400" x-text="formatPrice(finalTotal)"></span>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="p-4 sm:p-6">
                            <button type="submit" :disabled="submitting || shippingCalculating"
                                class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 disabled:from-slate-400 disabled:to-slate-400 text-white font-extrabold py-3.5 sm:py-4 rounded-xl shadow-lg shadow-indigo-200 dark:shadow-indigo-900/30 hover:shadow-xl hover:-translate-y-0.5 active:scale-[0.98] transition-all duration-200 flex justify-center items-center gap-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 cursor-pointer disabled:cursor-not-allowed disabled:shadow-none disabled:hover:translate-y-0 disabled:active:scale-100">
                                <span x-show="!submitting">{{ __('global.confirm_order') }}</span>
                                <svg x-show="!submitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <svg x-show="submitting" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                                <span x-show="submitting" x-text="'{{ __('global.processing') }}...'"></span>
                            </button>
                            <p class="text-xs text-center text-slate-400 dark:text-slate-500 mt-3">{{ __('global.checkout_secure_notice') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
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
            governorateId: '{{ old('governorate_id') }}',
            governorateName: '',
            cityId: '{{ old('city_id') }}',
            cityName: '',
            shippingCalculating: false,
            shippingAddress: '{{ old('shipping_address') }}',
            addressAutoFilled: false,

            get appliedCouponText() {
                if (!this.appliedCoupon) return '';
                if (this.appliedCoupon.type === 'percent') return `${this.appliedCoupon.code} — {{ __('global.coupon_discount_label') }} ${this.appliedCoupon.value}%`;
                return `${this.appliedCoupon.code} — {{ __('global.coupon_discount_label') }} {{ __('global.currency') }}${this.appliedCoupon.value}`;
            },

            formatPrice(price) {
                const value = Math.round(parseFloat(price || 0));
                return value.toLocaleString('ar-EG') + ' {{ __('global.currency') }}';
            },

            init() {
                if (this.governorateId) {
                    this.showCities(this.governorateId);
                    if (this.cityId) {
                        this.onCityChange();
                    }
                }
            },

            showCities(govId) {
                document.querySelectorAll('#city_select option[data-gov]').forEach(opt => {
                    opt.style.display = opt.dataset.gov == govId ? '' : 'none';
                    opt.disabled = opt.dataset.gov != govId;
                });
                document.querySelector('#city_select').disabled = false;
            },

            onGovernorateChange() {
                this.cityId = '';
                this.cityName = '';
                this.shipping = 0;
                this.finalTotal = this.baseTotal - this.discount;
                this.governorateName = '';
                if (!this.governorateId) {
                    document.querySelectorAll('#city_select option[data-gov]').forEach(opt => { opt.style.display = 'none'; });
                    document.querySelector('#city_select').disabled = true;
                    return;
                }
                const govSel = document.querySelector('#governorate_select option:checked');
                this.governorateName = govSel && govSel.value ? govSel.textContent : '';
                this.showCities(this.governorateId);
            },

            async onCityChange() {
                if (!this.governorateId || !this.cityId) return;
                const sel = document.querySelector('#city_select option:checked');
                this.cityName = sel ? sel.textContent : '';
                if (!this.addressAutoFilled && this.shippingAddress.trim() === '') {
                    this.shippingAddress = this.governorateName + ' - ' + this.cityName + '، ';
                    this.addressAutoFilled = true;
                    this.$nextTick(() => {
                        const ta = document.querySelector('[name=shipping_address]');
                        if (ta) { ta.focus(); ta.setSelectionRange(ta.value.length, ta.value.length); }
                    });
                }
                this.shippingCalculating = true;
                try {
                    const res = await fetch('{{ route('api.shipping.calculate') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({
                            governorate_id: this.governorateId,
                            city_id: this.cityId,
                            cart_total: this.baseTotal - this.discount,
                        })
                    });
                    if (res.ok) {
                        const data = await res.json();
                        this.shipping = data.final_cost || 0;
                        this.finalTotal = (this.baseTotal - this.discount) + this.shipping;
                    }
                } catch(e) { console.error('Shipping calc error:', e); }
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
