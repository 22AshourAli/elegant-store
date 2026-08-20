@extends('admin.layouts.app')

@section('title', 'جرد جديد')

@section('content')
<div class="max-w-lg mx-auto py-6 px-4">
    <h1 class="text-xl font-bold text-gray-900 mb-6">بدء جرد جديد</h1>

    <form method="POST" action="{{ route('admin.inventory.store') }}">
        @csrf
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">اختر الفرع</label>
                <select name="branch_id" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                    <option value="">اختر فرع...</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-700">
                سيتم جرد جميع المنتجات النشطة في الفرع المحدد. سجل العدد الفعلي لكل صنف.
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl transition text-sm">
                بدء الجرد
            </button>
        </div>
    </form>
</div>
@endsection
