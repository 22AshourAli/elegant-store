@extends('layouts.store')

@section('title', 'Mock Payment - Elegant Store')
@section('og_title', 'Mock Payment')

@section('content')
<div class="max-w-md mx-auto my-12 px-4">
    <!-- Header Card -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xl overflow-hidden transition-all duration-300">
        <!-- Top Banner -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6 text-white text-center">
            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3 animate-pulse">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-bold">{{ __('بوابة الدفع الافتراضية (التجريبية)') }}</h2>
            <p class="text-xs text-indigo-100 mt-1">{{ __('تظهر هذه الصفحة لأن مفاتيح Paymob غير مهيأة في ملف الإعدادات') }}</p>
        </div>

        <div class="p-6 space-y-6">
            <!-- Order Details -->
            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-4 border border-gray-100 dark:border-gray-800 space-y-3">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500 dark:text-gray-400">{{ __('رقم الطلب') }}</span>
                    <span class="font-bold text-gray-900 dark:text-white">#{{ $order->id }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500 dark:text-gray-400">{{ __('طريقة الدفع المختارة') }}</span>
                    <span class="px-2.5 py-1 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-lg font-semibold text-xs">
                        {{ $method === 'card' ? __('فيزا بنكية / كارت ائتمان') : __('محفظة إلكترونية') }}
                    </span>
                </div>
                <hr class="border-gray-200 dark:border-gray-800">
                <div class="flex justify-between items-center">
                    <span class="text-base font-semibold text-gray-700 dark:text-gray-300">{{ __('المبلغ الإجمالي') }}</span>
                    <span class="text-xl font-black text-indigo-600 dark:text-indigo-400">
                        {{ (int) round($order->total) }} {{ __('global.currency') }}
                    </span>
                </div>
            </div>

            <!-- Demo Card Detail Fields (Visual Mock only) -->
            @if($method === 'card')
            <div class="space-y-4 text-start">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ __('بيانات كارت الائتمان (محاكاة)') }}</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">{{ __('رقم الكارت') }}</label>
                        <input type="text" value="4000 1234 5678 9010" disabled class="w-full bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 rounded-xl px-4 py-2.5 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">{{ __('تاريخ الانتهاء') }}</label>
                            <input type="text" value="12 / 29" disabled class="w-full bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 rounded-xl px-4 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">{{ __('رمز الأمان CVV') }}</label>
                            <input type="text" value="***" disabled class="w-full bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 rounded-xl px-4 py-2.5 text-sm">
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="space-y-4 text-start">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ __('بيانات المحفظة (محاكاة)') }}</h3>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">{{ __('رقم المحفظة (فودافون كاش / اتصالات / إلخ)') }}</label>
                    <input type="text" value="{{ $order->user->phone ?? '01000000000' }}" disabled class="w-full bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 rounded-xl px-4 py-2.5 text-sm">
                </div>
            </div>
            @endif

            <!-- Warning Notice -->
            <div class="p-3 bg-amber-50 dark:bg-amber-950/20 text-amber-800 dark:text-amber-300 rounded-xl border border-amber-200/50 dark:border-amber-900/50 text-xs flex gap-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ __('ملاحظة: اضغط على خيار النجاح لتسجيل الطلب وتجربة حالة "جاري التحضير"، أو إلغاء الدفع لاختبار دورة الدفع الملغاة.') }}</span>
            </div>

            <!-- Action Buttons Form -->
            <form action="{{ route('payment.mock.process', ['order' => $order->id, 'method' => $method]) }}" method="POST" class="grid grid-cols-2 gap-4">
                @csrf
                <button type="submit" name="status" value="success" class="flex items-center justify-center gap-2 py-3 px-4 bg-green-600 hover:bg-green-700 active:scale-[0.98] text-white font-bold rounded-2xl shadow-lg shadow-green-500/20 transition-all duration-150">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ __('دفع ناجح') }}</span>
                </button>
                <button type="submit" name="status" value="failed" class="flex items-center justify-center gap-2 py-3 px-4 bg-red-600 hover:bg-red-700 active:scale-[0.98] text-white font-bold rounded-2xl shadow-lg shadow-red-500/20 transition-all duration-150">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ __('إلغاء / فشل الدفع') }}</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
