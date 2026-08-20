@extends('admin.layouts.app')

@section('title', "طباعة باركود - {$variant->name}")

@section('content')
<div class="max-w-2xl mx-auto py-6 px-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-gray-900">طباعة باركود</h1>
        <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition">طباعة</button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
        <p class="text-sm font-medium text-gray-900 mb-1">{{ $variant->product->name }}</p>
        <p class="text-xs text-gray-500 mb-4">{{ $variant->color }} / {{ $variant->size }}</p>
        <div class="flex justify-center mb-2">
            {!! $barcodeHtml !!}
        </div>
        <p class="font-mono font-bold text-base tracking-wider">{{ $variant->barcode }}</p>
    </div>

    <style>
        @media print {
            body * { visibility: hidden; }
            .bg-white, .bg-white * { visibility: visible; }
            .bg-white { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); }
        }
    </style>
</div>
@endsection
