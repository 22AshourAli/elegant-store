@extends('layouts.store')

@section('seo')
    {!! SEO::generate() !!}
@endsection

@section('content')
<div class="bg-gray-50 dark:bg-gray-800/50 py-8 border-b border-gray-200 dark:border-gray-700">
    <div class="container mx-auto px-4">
        <nav class="flex text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 space-x-reverse md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('global.home') }}</a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mx-1 {{ app()->getLocale() === 'ar' ? 'transform rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="text-gray-800 dark:text-gray-200 font-semibold">{{ __('global.return_policy') }}</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">{{ __('global.return_policy') }}</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-12 max-w-4xl">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
        
        <div class="mb-8">
            <h2 class="text-xl font-bold mb-4 text-indigo-600 dark:text-indigo-400">{{ __('return.policy_title') }}</h2>
            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">{{ __('return.policy_intro') }}</p>
        </div>

        <div class="space-y-6">
            <div class="flex gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">1</div>
                <div>
                    <h3 class="font-bold text-lg mb-1">{{ __('return.rule_1_title') }}</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">{{ __('return.rule_1_desc') }}</p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">2</div>
                <div>
                    <h3 class="font-bold text-lg mb-1">{{ __('return.rule_2_title') }}</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">{{ __('return.rule_2_desc') }}</p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">3</div>
                <div>
                    <h3 class="font-bold text-lg mb-1">{{ __('return.rule_3_title') }}</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">{{ __('return.rule_3_desc') }}</p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">4</div>
                <div>
                    <h3 class="font-bold text-lg mb-1">{{ __('return.rule_4_title') }}</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">{{ __('return.rule_4_desc') }}</p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">5</div>
                <div>
                    <h3 class="font-bold text-lg mb-1">{{ __('return.rule_5_title') }}</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">{{ __('return.rule_5_desc') }}</p>
                </div>
            </div>
        </div>

        <div class="mt-10 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800 rounded-xl p-6">
            <h3 class="font-bold text-lg mb-2 flex items-center gap-2 text-amber-700 dark:text-amber-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ __('return.how_to_return_title') }}
            </h3>
            <p class="text-amber-700 dark:text-amber-300 text-sm">{{ __('return.how_to_return_desc') }}</p>
            <a href="mailto:{{ config('mail.from.address') }}" class="mt-4 inline-block bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-6 rounded-lg text-sm transition">{{ __('return.contact_us_btn') }}</a>
        </div>
    </div>
</div>
@endsection