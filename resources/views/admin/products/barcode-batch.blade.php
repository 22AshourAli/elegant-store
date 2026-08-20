@extends('admin.layouts.app')

@section('title', 'طباعة باركود متعددة')

@section('content')
<div class="max-w-4xl mx-auto py-6 px-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-gray-900">طباعة باركود</h1>
        <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition">طباعة الكل</button>
    </div>

    @if($barcodes->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm p-12 text-center text-gray-400 text-sm">لا توجد باركود للطباعة</div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            @foreach($barcodes as $item)
                <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                    <p class="text-[10px] text-gray-600 font-medium mb-1">{{ $item['variant']->product->name }}</p>
                    <p class="text-[9px] text-gray-400 mb-2">{{ $item['variant']->color }} / {{ $item['variant']->size }}</p>
                    <div class="flex justify-center mb-1">{!! $item['html'] !!}</div>
                    <p class="font-mono text-[10px] font-bold">{{ $item['variant']->barcode }}</p>
                </div>
            @endforeach
        </div>
    @endif

    <style>
        @media print {
            body * { visibility: hidden; }
            .grid, .grid * { visibility: visible; }
            .grid { position: absolute; top: 0; left: 0; }
        }
    </style>
</div>
@endsection
