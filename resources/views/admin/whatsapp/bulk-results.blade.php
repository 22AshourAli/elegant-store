@extends('admin.layouts.app')
@section('page-title', 'نتائج الإرسال الجماعي')
@section('content')
<div class="bg-white dark:bg-gray-800 rounded shadow p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-lg">روابط واتساب للعملاء ({{ $results->count() }})</h3>
        <button onclick="copyAllLinks()" class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 transition">
            نسخ جميع الروابط
        </button>
    </div>

    <div class="text-sm text-gray-500 mb-4 p-4 bg-amber-50 dark:bg-amber-950/20 rounded">
        <strong>ملاحظة:</strong> واتساب لا يدعم الإرسال الجماعي التلقائي. كل رابط يفتح محادثة واتساب مع عميل - اضغط على كل رابط لإرسال الرسالة يدوياً.
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-right">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="p-3">العميل</th>
                    <th class="p-3">رقم الهاتف</th>
                    <th class="p-3">الإجراء</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $item)
                <tr class="border-t dark:border-gray-700">
                    <td class="p-3">{{ $item['name'] }}</td>
                    <td class="p-3 text-xs text-gray-500 ltr" dir="ltr">{{ $item['phone'] }}</td>
                    <td class="p-3">
                        <a href="{{ $item['wa_link'] }}" target="_blank"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white text-xs font-bold rounded-lg hover:bg-green-700 transition">
                            فتح واتساب
                        </a>
                        <button onclick="copyLink('{{ $item['wa_link'] }}')"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-600 text-white text-xs font-bold rounded-lg hover:bg-gray-700 transition">
                            نسخ الرابط
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.whatsapp.bulk') }}" class="text-indigo-600 hover:underline text-sm font-semibold">
            إرسال رسالة أخرى
        </a>
    </div>
</div>

@push('scripts')
<script>
function copyLink(url) {
    navigator.clipboard.writeText(url).then(function() {
        alert('تم نسخ الرابط');
    });
}
function copyAllLinks() {
    const links = Array.from(document.querySelectorAll('a[href*="wa.me"]'))
        .map(a => a.href).join('\n');
    navigator.clipboard.writeText(links).then(function() {
        alert('تم نسخ جميع الروابط');
    });
}
</script>
@endpush
@endsection
