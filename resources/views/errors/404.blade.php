@extends('layouts.store')

@section('content')
<div class="container mx-auto px-4 py-20 text-center">
    <h1 class="text-8xl font-extrabold text-indigo-600 mb-4">404</h1>
    <p class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{ __('global.page_not_found') }}</p>
    <p class="text-gray-500 mb-8">{{ __('global.page_not_found_desc') }}</p>
    <a href="{{ route('home') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg transition-colors">{{ __('global.home') }}</a>
</div>
@endsection
