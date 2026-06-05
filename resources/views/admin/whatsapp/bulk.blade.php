@extends('admin.layouts.app')
@section('page-title', 'إرسال جماعي')
@section('content')
<div class="max-w-2xl">
    <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
        <form method="POST" action="{{ route('admin.whatsapp.bulk.send') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">قناة الإرسال</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="channel" value="whatsapp" checked
                            class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm">واتساب</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="channel" value="email"
                            class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm">البريد الإلكتروني</span>
                    </label>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">الجمهور المستهدف</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="audience" value="all" checked
                            class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm">جميع العملاء ({{ $totalCustomers }})</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="audience" value="previous_buyers"
                            class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm">المشترين سابقاً فقط ({{ $previousBuyers }})</span>
                    </label>
                </div>
            </div>

            <div class="mb-4" id="subjectField" style="display:none">
                <label class="block text-sm font-medium mb-1">عنوان البريد</label>
                <input type="text" name="subject" value="{{ old('subject') }}"
                    class="w-full border rounded px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600"
                    placeholder="عنوان الرسالة">
                @error('subject')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">نص الرسالة</label>
                <textarea name="message" rows="8" required
                    class="w-full border rounded px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 @error('message') border-red-500 @enderror"
                    placeholder="اكتب رسالتك هنا...">{{ old('message') }}</textarea>
                @error('message')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 transition text-sm">
                إرسال
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('input[name="channel"]').forEach(el => {
    el.addEventListener('change', function() {
        document.getElementById('subjectField').style.display = this.value === 'email' ? 'block' : 'none';
    });
});
</script>
@endpush
@endsection
