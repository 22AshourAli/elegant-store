@extends('layouts.store')

@section('content')
<script>
var CHECKOUT_DATA = {
    baseTotal: {{ (int) round($baseTotal) }},
    discount: {{ (int) round($discount) }},
    shipping: {{ (int) round($shipping) }},
    finalTotal: {{ (int) round($finalTotal) }},
    oldGovId: '{{ old('governorate_id') }}',
    oldCityId: '{{ old('city_id') }}',
    currency: '{{ __('global.currency') }}',
    freeText: '{{ __('global.free') }}',
    shippingCalcText: '{{ __('global.shipping_calculating') }}',
    csrfToken: '{{ csrf_token() }}',
    shippingApiUrl: '{{ route('api.shipping.calculate') }}',
    isFirstOrder: {{ auth()->user()->orders()->where('status', '!=', 'cancelled')->count() === 0 ? 'true' : 'false' }},
    shippingKnown: {{ isset($shippingKnown) && $shippingKnown ? 'true' : 'false' }},
};
</script>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50/30 dark:from-gray-950 dark:via-gray-900 dark:to-indigo-950/20 py-6 sm:py-10" x-data="checkoutPage({
    baseTotal: {{ (int) round($baseTotal) }},
    discount: {{ (int) round($discount) }},
    shipping: {{ (int) round($shipping) }},
    finalTotal: {{ (int) round($finalTotal) }},
    appliedCoupon: @json($appliedCoupon ? ['code' => $appliedCoupon->code, 'type' => $appliedCoupon->type, 'value' => $appliedCoupon->value] : null),
    governorates: @json($governorates),
})">
    <div class="max-w-6xl mx-auto px-3 sm:px-4 lg:px-6">

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
        <div class="flex items-start gap-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/50 rounded-2xl p-4 mb-6 shadow-sm" x-data="{ show: true }" x-show="show">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm font-medium text-red-700 dark:text-red-400 flex-1">{{ session('error') }}</p>
            <button @click="show = false" class="text-red-400 hover:text-red-600 transition">&times;</button>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/50 rounded-2xl p-4 mb-6 shadow-sm" x-data="{ show: true }" x-show="show">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="flex-1">
                    <p class="text-sm font-bold text-red-700 dark:text-red-400 mb-1.5">تأكد من البيانات التالية:</p>
                    <ul class="text-sm text-red-600 dark:text-red-300 space-y-1">
                        @foreach($errors->all() as $error)
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>{{ $error }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <button @click="show = false" class="text-red-400 hover:text-red-600 transition shrink-0">&times;</button>
            </div>
        </div>
        @endif

        <form action="{{ route('checkout.store') }}" method="POST" class="checkout-form" @submit.prevent="submitOrder">
            @csrf
            <div class="grid lg:grid-cols-5 gap-4 sm:gap-6 lg:gap-8">

                <!-- LEFT -->
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
                                class="w-full border rounded-xl shadow-sm px-4 py-3 text-sm font-semibold outline-none transition-all bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white @error('phone') border-red-400 dark:border-red-500 ring-1 ring-red-300 dark:ring-red-700 @enderror border-slate-200/60 dark:border-slate-700/60 focus:border-indigo-400 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800/40">
                            @error('phone')
                            <p class="mt-1.5 text-xs text-red-500 font-semibold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Shipping -->
                    <div class="bg-white dark:bg-gray-800/80 rounded-2xl p-4 sm:p-6 border border-slate-200/60 dark:border-slate-700/60 shadow-sm backdrop-blur-sm">
                        <div class="flex items-center gap-2.5 mb-5 pb-3 border-b border-slate-100 dark:border-slate-700/60">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">{{ __('global.shipping_info') }}</h2>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.governorate') }} <span class="text-red-500">*</span></label>
                                <select name="governorate_id" id="governorate_id" required x-model="governorateId"
                                    class="w-full border rounded-xl shadow-sm px-4 py-3 text-sm font-semibold outline-none transition-all appearance-none bg-[length:16px] bg-[right_12px_center] bg-no-repeat dark:bg-[right_12px_center] bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white @error('governorate_id') border-red-400 dark:border-red-500 ring-1 ring-red-300 dark:ring-red-700 @enderror border-slate-200/60 dark:border-slate-700/60 focus:border-indigo-400 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800/40"
                                    style="background-image: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\")">
                                    <option value="">{{ __('global.select_governorate') }}</option>
                                    @forelse($governorates as $gov)
                                    <option value="{{ $gov['id'] }}" {{ old('governorate_id') == $gov['id'] ? 'selected' : '' }}>{{ $gov['name'] }}</option>
                                    @empty
                                    <option value="" disabled>⚠️ {{ __('global.no_governorates') }}</option>
                                    @endforelse
                                </select>
                                @error('governorate_id')
                                <p class="mt-1.5 text-xs text-red-500 font-semibold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.city') }} <span class="text-red-500">*</span></label>
                                <select name="city_id" id="city_id" required x-model="cityId"
                                    class="w-full border rounded-xl shadow-sm px-4 py-3 text-sm font-semibold outline-none transition-all appearance-none bg-[length:16px] bg-[right_12px_center] bg-no-repeat dark:bg-[right_12px_center] disabled:opacity-50 disabled:cursor-not-allowed bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white @error('city_id') border-red-400 dark:border-red-500 ring-1 ring-red-300 dark:ring-red-700 @enderror border-slate-200/60 dark:border-slate-700/60 focus:border-indigo-400 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800/40"
                                    style="background-image: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\")">
                                    <option value="">{{ __('global.select_city') }}</option>
                                    @foreach($governorates as $gov)
                                    @foreach($gov['cities'] ?? [] as $city)
                                    <option value="{{ $city['id'] }}" data-gov="{{ $gov['id'] }}" style="display:none" {{ old('city_id') == $city['id'] ? 'selected' : '' }}>{{ $city['name'] }}</option>
                                    @endforeach
                                    @endforeach
                                </select>
                                @error('city_id')
                                <p class="mt-1.5 text-xs text-red-500 font-semibold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-3 sm:mt-4">
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.shipping_address_full') }} <span class="text-red-500">*</span></label>
                            <textarea name="shipping_address" required rows="2" placeholder="{{ __('global.address_placeholder') }}"
                                x-model="shippingAddress"
                                @input="addressAutoFilled = false"
                                class="w-full border rounded-xl shadow-sm px-4 py-3 text-sm font-semibold outline-none transition-all resize-none bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white @error('shipping_address') border-red-400 dark:border-red-500 ring-1 ring-red-300 dark:ring-red-700 @enderror border-slate-200/60 dark:border-slate-700/60 focus:border-indigo-400 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800/40"></textarea>
                            @error('shipping_address')
                            <p class="mt-1.5 text-xs text-red-500 font-semibold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <div class="mt-3 sm:mt-4">
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700 dark:text-slate-300">{{ __('global.order_notes') }}</label>
                            <textarea name="notes" rows="1" placeholder="{{ __('global.order_notes_placeholder') }}"
                                class="w-full border border-slate-200/60 dark:border-slate-700/60 rounded-xl shadow-sm focus:border-indigo-400 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800/40 bg-white/70 dark:bg-slate-900/60 text-slate-900 dark:text-white px-4 py-3 text-sm font-semibold outline-none transition-all resize-none">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <!-- Payment -->
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
                        @error('payment_method') <p class="mt-2 text-xs text-red-500 font-semibold flex items-center gap-1"><svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- RIGHT: Summary -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800/80 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-lg shadow-slate-200/50 dark:shadow-slate-900/30 sticky top-24 overflow-hidden backdrop-blur-sm">

                        <div class="p-4 sm:p-6 border-b border-slate-100 dark:border-slate-700/60">
                            <div class="flex items-center justify-between">
                                <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    {{ __('global.your_order') }}
                                </h2>
                                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-lg">{{ count($cartItems) }} {{ __('global.items_count') }}</span>
                            </div>
                        </div>

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
                                <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-lg">x{{ $item['quantity'] }}</span>
                                    <span class="font-extrabold text-sm text-slate-900 dark:text-white">{{ (int) round($item['price'] * $item['quantity']) }} {{ __('global.currency') }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>

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

                        <div class="px-4 sm:px-6 py-4 space-y-3 border-t border-slate-100 dark:border-slate-700/60">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500 dark:text-slate-400">{{ __('global.products_total') }}</span>
                                <span class="font-bold text-slate-900 dark:text-white product-total-display">{{ number_format($baseTotal) . ' ' . __('global.currency') }}</span>
                            </div>
                            <div class="flex justify-between text-sm discount-row" id="discount-row" @if(!($discount > 0 && $appliedCoupon)) style="display:none" @endif>
                                <span class="text-emerald-600 dark:text-emerald-400">{{ __('global.coupon_discount_label') }}</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400 discount-display">-{{ number_format($discount) . ' ' . __('global.currency') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500 dark:text-slate-400">{{ __('global.shipping_cost_label') }}</span>
                                <span>
                                    <span class="shipping-calculating font-bold text-slate-400 dark:text-slate-500 text-xs italic" style="display:none">{{ __('global.shipping_calculating') }}</span>
                                    <span class="font-bold shipping-display @if(isset($shippingKnown) && !$shippingKnown) text-slate-400 @elseif($shipping > 0) text-slate-900 dark:text-white @else text-emerald-500 text-xs @endif">{{ isset($shippingKnown) && !$shippingKnown ? '—' : ($shipping > 0 ? number_format($shipping) . ' ' . __('global.currency') : __('global.free')) }}</span>
                                </span>
                            </div>
                        </div>

                        <div class="px-4 sm:px-6 py-4 bg-gradient-to-r from-indigo-50/80 to-purple-50/80 dark:from-indigo-950/30 dark:to-purple-950/30 border-t border-slate-100 dark:border-slate-700/60">
                            <div class="flex justify-between items-baseline">
                                <span class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white">{{ __('global.final_total') }}</span>
                                <span class="text-xl sm:text-2xl font-black text-indigo-600 dark:text-indigo-400 final-total-display">{{ number_format($finalTotal) . ' ' . __('global.currency') }}</span>
                            </div>
                        </div>

                        <div class="p-4 sm:p-6">
                            <button type="submit" x-ref="submitBtn"
                                class="w-full relative overflow-hidden bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 disabled:from-slate-400 disabled:to-slate-400 text-white font-extrabold py-3.5 sm:py-4 rounded-xl shadow-lg shadow-indigo-200 dark:shadow-indigo-900/30 hover:shadow-xl hover:-translate-y-0.5 active:scale-[0.98] transition-all duration-200 flex justify-center items-center gap-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 cursor-pointer disabled:cursor-not-allowed disabled:shadow-none disabled:hover:translate-y-0 disabled:active:scale-100">
                                <span x-show="!submitting" style="display:flex" class="flex items-center gap-2 transition-opacity duration-200">
                                    <span>{{ __('global.confirm_order') }}</span>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span x-show="submitting" style="display:none" class="flex items-center gap-2.5 transition-opacity duration-200">
                                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                                    <span>{{ __('global.processing') }}...</span>
                                </span>
                            </button>
                            <p class="text-xs text-center text-slate-400 dark:text-slate-500 mt-3">{{ __('global.checkout_secure_notice') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Processing Overlay -->
<div x-show="submitting" x-cloak
    class="fixed inset-0 z-[200] flex items-center justify-center bg-gray-900/60 dark:bg-gray-950/80 backdrop-blur-md transition-all duration-300"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-2xl rounded-3xl shadow-2xl border border-indigo-200/50 dark:border-indigo-700/30 p-8 sm:p-10 max-w-sm mx-4 text-center"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4">
        <div class="relative mx-auto w-16 h-16 mb-5">
            <div class="absolute inset-0 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 animate-ping opacity-20"></div>
            <div class="relative w-16 h-16 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <svg class="w-8 h-8 text-white animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
            </div>
        </div>
        <h3 class="text-lg font-extrabold text-gray-900 dark:text-white mb-1">{{ __('global.processing_order_title') }}</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">{{ __('global.processing_order_body') }}</p>
        <div class="flex justify-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-indigo-500 animate-bounce" style="animation-delay:0s"></span>
            <span class="w-2 h-2 rounded-full bg-indigo-500 animate-bounce" style="animation-delay:0.15s"></span>
            <span class="w-2 h-2 rounded-full bg-indigo-500 animate-bounce" style="animation-delay:0.3s"></span>
        </div>
    </div>
</div>

<!-- Toast -->
<div x-data="toastHandler()" @toast.window="show($event.detail)" x-show="visible" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
     class="fixed bottom-6 right-6 z-[100] max-w-sm w-full pointer-events-auto" style="display:none">
    <div class="flex items-start gap-3 rounded-2xl shadow-2xl border p-4 backdrop-blur-xl"
         :class="type === 'error' ? 'bg-red-50 dark:bg-red-950/90 border-red-200 dark:border-red-800' : 'bg-emerald-50 dark:bg-emerald-950/90 border-emerald-200 dark:border-emerald-800'">
        <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center"
             :class="type === 'error' ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' : 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400'">
            <svg x-show="type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <svg x-show="type !== 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold" :class="type === 'error' ? 'text-red-800 dark:text-red-300' : 'text-emerald-800 dark:text-emerald-300'" x-text="message"></p>
        </div>
        <button @click="dismiss()" class="flex-shrink-0 p-1 rounded-lg hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>

@push('scripts')
<script>
function toastHandler() {
    return {
        visible: false, message: '', type: 'success', timer: null,
        show(detail) { this.message = detail.message || ''; this.type = detail.type || 'success'; this.visible = true; clearTimeout(this.timer); this.timer = setTimeout(() => { this.visible = false; }, 5000); },
        dismiss() { this.visible = false; clearTimeout(this.timer); }
    };
}

document.addEventListener('DOMContentLoaded', function () {
    var govSelect = document.getElementById('governorate_id');
    var citySelect = document.getElementById('city_id');
    var addrInput = document.querySelector('[name=shipping_address]');
    var D = typeof CHECKOUT_DATA !== 'undefined' ? CHECKOUT_DATA : {};

    function fmtPrice(v) {
        const locale = document.documentElement.lang === 'ar' ? 'ar-EG' : 'en-US';
        return Math.round(parseFloat(v || 0)).toLocaleString(locale) + ' ' + D.currency;
    }

    function updateShippingDisplay(cost, known) {
        var sd = document.querySelector('.shipping-display');
        var sc = document.querySelector('.shipping-calculating');
        var ft = document.querySelector('.final-total-display');
        if (sc) sc.style.display = 'none';
        if (sd) {
            cost = parseFloat(cost || 0);
            if (known === false) {
                sd.textContent = '\u2014';
                sd.className = 'font-bold shipping-display text-slate-400';
            } else if (cost === 0) {
                sd.textContent = D.freeText;
                sd.className = 'font-bold shipping-display text-emerald-500 text-xs';
            } else {
                sd.textContent = fmtPrice(cost);
                sd.className = 'font-bold shipping-display text-slate-900 dark:text-white';
            }
        }
        if (ft) {
            var total = (D.baseTotal - D.discount) + cost;
            ft.textContent = fmtPrice(total);
        }
        D._shipping = cost;
    }

    function fetchShipping(govId, cityId) {
        if (D.isFirstOrder) { updateShippingDisplay(0, true); return; }
        var sc = document.querySelector('.shipping-calculating');
        if (sc) sc.style.display = 'inline';
        if (!govId) { updateShippingDisplay(0, false); return; }
        var body = { governorate_id: govId, cart_total: D.baseTotal - D.discount };
        if (cityId) body.city_id = cityId;
        fetch(D.shippingApiUrl || '/api/shipping/calculate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': D.csrfToken },
            body: JSON.stringify(body)
        }).then(function(r) {
            if (!r.ok) throw new Error('API error');
            return r.json();
        }).then(function(data) {
            var cost = typeof data.final_cost === 'number' ? data.final_cost : 0;
            updateShippingDisplay(cost, true);
        }).catch(function() { updateShippingDisplay(0, false); });
    }

    function filterCities(govId) {
        var prevCity = citySelect.value || D.oldCityId;
        citySelect.disabled = !govId;
        citySelect.querySelectorAll('option[data-gov]').forEach(function (opt) {
            var show = opt.getAttribute('data-gov') == govId;
            opt.style.display = show ? '' : 'none';
            opt.disabled = !show;
        });
        if (prevCity) {
            var match = citySelect.querySelector('option[value="' + prevCity + '"][data-gov="' + govId + '"]');
            if (match) { citySelect.value = prevCity; }
            else { citySelect.value = ''; }
        } else { citySelect.value = ''; }
    }

    if (govSelect) {
        govSelect.addEventListener('change', function () {
            var g = govSelect.value;
            filterCities(g);
            if (addrInput) {
                addrInput.value = g ? (govSelect.options[govSelect.selectedIndex]?.text || '') + ' - ' : '';
            }
            fetchShipping(g, citySelect.value);
        });
    }

    if (citySelect) {
        citySelect.addEventListener('change', function () {
            if (addrInput && govSelect && citySelect) {
                var govName = govSelect.options[govSelect.selectedIndex]?.text || '';
                var cityName = citySelect.options[citySelect.selectedIndex]?.text || '';
                if (govName && cityName) {
                    addrInput.value = govName + ' - ' + cityName + '، ';
                }
            }
            fetchShipping(govSelect.value, citySelect.value);
        });
    }

    if (govSelect && govSelect.value) {
        filterCities(govSelect.value);
        if (D.oldCityId && citySelect) {
            citySelect.value = D.oldCityId;
            fetchShipping(govSelect.value, citySelect.value);
        } else if (addrInput && addrInput.value.trim() === '') {
            var gn = govSelect.options[govSelect.selectedIndex]?.text || '';
            addrInput.value = gn + ' - ';
        }
    }


});

document.addEventListener('alpine:init', () => {
    Alpine.data('checkoutPage', (initial) => ({
        baseTotal: initial.baseTotal,
        discount: initial.discount,
        finalTotal: initial.finalTotal,
        appliedCoupon: initial.appliedCoupon,
        couponCode: '',
        couponError: '',
        couponLoading: false,
        submitting: false,
        submitTimeout: null,

        init() {
            this.submitting = false;
            this.submitTimeout = null;
        },

        governorates: initial.governorates || [],
        addressAutoFilled: false,
        governorateId: '{{ old('governorate_id') }}',
        cityId: '{{ old('city_id') }}',
        shippingAddress: '{{ old('shipping_address') }}',

        get appliedCouponText() {
            if (!this.appliedCoupon) return '';
            if (this.appliedCoupon.type === 'percent') return `${this.appliedCoupon.code} — {{ __('global.coupon_discount_label') }} ${this.appliedCoupon.value}%`;
            return `${this.appliedCoupon.code} — {{ __('global.coupon_discount_label') }} {{ __('global.currency') }}${this.appliedCoupon.value}`;
        },

        formatPrice(price) {
            var value = Math.round(parseFloat(price || 0));
            var locale = document.documentElement.lang === 'ar' ? 'ar-EG' : 'en-US';
            return value.toLocaleString(locale) + ' {{ __('global.currency') }}';
        },

        get shipping() {
            return typeof CHECKOUT_DATA !== 'undefined' ? (CHECKOUT_DATA._shipping ?? CHECKOUT_DATA.shipping) : 0;
        },

        set shipping(v) {
            if (typeof CHECKOUT_DATA !== 'undefined') CHECKOUT_DATA._shipping = v;
        },

        updateDisplay() {
            var pt = document.querySelector('.product-total-display');
            if (pt) pt.textContent = this.formatPrice(this.baseTotal);

            var dr = document.getElementById('discount-row');
            var dd = document.querySelector('.discount-display');
            if (dr && dd) {
                if (this.discount > 0 && this.appliedCoupon) {
                    dr.style.display = '';
                    dd.textContent = '-' + this.formatPrice(this.discount);
                } else {
                    dr.style.display = 'none';
                }
            }

            var ft = document.querySelector('.final-total-display');
            if (ft) {
                var ship = this.shipping;
                ft.textContent = this.formatPrice((this.baseTotal - this.discount) + ship);
            }
        },

        applyCoupon(code) {
            this.couponError = '';
            if (!code) return;
            this.couponLoading = true;
            fetch('{{ route('coupon.apply') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ code })
            }).then(async function(res) {
                var data = await res.json();
                if (!res.ok) throw new Error(data.message || '{{ __('global.coupon_error') }}');
                this.appliedCoupon = data.coupon;
                this.baseTotal = Number(data.baseTotal);
                this.discount = Number(data.discount);
                if (typeof CHECKOUT_DATA !== 'undefined') {
                    CHECKOUT_DATA.baseTotal = Number(data.baseTotal);
                    CHECKOUT_DATA.discount = Number(data.discount);
                }
                this.couponCode = '';
                this.couponLoading = false;
                this.updateDisplay();
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message, type: 'success' } }));
            }.bind(this)).catch(function(err) {
                this.couponError = err.message;
                this.couponLoading = false;
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: err.message, type: 'error' } }));
            }.bind(this));
        },

        removeCoupon() {
            this.couponError = '';
            this.couponLoading = true;
            fetch('{{ route('coupon.remove') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(async function(res) {
                var data = await res.json();
                this.appliedCoupon = null;
                this.baseTotal = Number(data.baseTotal);
                this.discount = Number(data.discount);
                if (typeof CHECKOUT_DATA !== 'undefined') {
                    CHECKOUT_DATA.baseTotal = Number(data.baseTotal);
                    CHECKOUT_DATA.discount = Number(data.discount);
                }
                this.couponLoading = false;
                this.updateDisplay();
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message, type: 'success' } }));
            }.bind(this)).catch(function() { this.couponLoading = false; }.bind(this));
        },

        submitOrder(e) {
            if (this.submitting) return;
            this.submitting = true;
            this.submitTimeout = setTimeout(() => {
                if (this.submitting) {
                    this.submitting = false;
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { message: '{{ __('global.checkout_timeout') }}', type: 'error' }
                    }));
                }
            }, 45000);
            e.target.submit();
        }
    }));
});
</script>
@endpush
@endsection
