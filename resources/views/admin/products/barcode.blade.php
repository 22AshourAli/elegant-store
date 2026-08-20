@extends('admin.layouts.app')

@section('title', "باركود - {$variant->name}")

@section('content')
<div class="max-w-lg mx-auto py-6 px-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-gray-900">باركود</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.barcode.print', $variant) }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3 py-1.5 rounded-xl transition">طباعة</a>
            <a href="{{ route('admin.barcode.download', $variant) }}" class="bg-white border border-gray-200 text-gray-700 text-xs font-bold px-3 py-1.5 rounded-xl hover:bg-gray-50 transition">تحميل PNG</a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
        <p class="text-sm text-gray-600 mb-1">{{ $variant->product->name }}</p>
        <p class="text-xs text-gray-400 mb-4">{{ $variant->color }} / {{ $variant->size }}</p>

        <div class="flex justify-center mb-3">
            {!! $barcodeHtml !!}
        </div>

        <p class="text-lg font-mono font-bold text-gray-900 tracking-wider">{{ $variant->barcode }}</p>
        <p class="text-[10px] text-gray-400 mt-1">SKU: {{ $variant->sku }}</p>
    </div>
</div>
@endsection
