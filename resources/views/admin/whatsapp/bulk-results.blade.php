@extends('admin.layouts.app')
@section('page-title', 'نتائج الإرسال الجماعي')
@section('content')
<div class="bg-white dark:bg-gray-800 rounded shadow p-6">
    {{-- Summary header --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="bg-indigo-50 dark:bg-indigo-950/30 rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">{{ $stats['total'] }}</p>
            <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">إجمالي</p>
        </div>
        @if(isset($stats['email_sent']))
        <div class="bg-green-50 dark:bg-green-950/30 rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $stats['email_sent'] }}</p>
            <p class="text-xs text-green-600 dark:text-green-400 mt-1">بريد مرسل</p>
        </div>
        @endif
        @if(isset($stats['email_failed']))
        <div class="bg-red-50 dark:bg-red-950/30 rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $stats['email_failed'] }}</p>
            <p class="text-xs text-red-600 dark:text-red-400 mt-1">بريد فاشل</p>
        </div>
        @endif
        <div class="bg-amber-50 dark:bg-amber-950/30 rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ $stats['wa_links'] }}</p>
            <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">روابط واتساب</p>
        </div>
    </div>

    @if(count($results) > 0)
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-lg">روابط واتساب ({{ count($results) }})</h3>
        <button onclick="copyAllLinks()"
                class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 transition">
            نسخ جميع الروابط
        </button>
    </div>

    <div class="text-sm text-gray-500 mb-4 p-4 bg-amber-50 dark:bg-amber-950/20 rounded">
        <strong>ملاحظة:</strong> كل رابط يفتح محادثة واتساب مع العميل مباشرة في <strong>تبويب جديد</strong>.
        الروابط تستخدم <code>api.whatsapp.com</code> لتفتح التطبيق الأصلي على الموبايل أو واتساب ويب.
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
                        <a href="{{ $item['wa_link'] }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white text-xs font-bold rounded-lg hover:bg-green-700 transition">
                            فتح واتساب
                        </a>
                        <button onclick="copyLink('{{ $item['wa_link'] }}', this)"
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-600 text-white text-xs font-bold rounded-lg hover:bg-gray-700 transition">
                            نسخ الرابط
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="p-8 text-center text-gray-400">
        <p class="text-lg mb-2">جميع العملاء لديهم بريد إلكتروني</p>
        <p class="text-sm">تم إرسال البريد الإلكتروني للجميع، لا توجد روابط واتساب.</p>
    </div>
    @endif

    <div class="mt-4 flex gap-3">
        <a href="{{ route('admin.whatsapp.bulk') }}" class="text-indigo-600 hover:underline text-sm font-semibold">
            إرسال رسالة أخرى
        </a>
        <a href="{{ route('admin.whatsapp.index') }}" class="text-gray-600 hover:underline text-sm font-semibold">
            العودة لقائمة العملاء
        </a>
    </div>
</div>

@push('scripts')
<script>
function copyLink(url, btn) {
    navigator.clipboard.writeText(url).then(function() {
        const original = btn.innerHTML;
        btn.innerHTML = 'تم النسخ ✓';
        btn.classList.remove('bg-gray-600', 'hover:bg-gray-700');
        btn.classList.add('bg-green-600');
        setTimeout(() => {
            btn.innerHTML = original;
            btn.classList.remove('bg-green-600');
            btn.classList.add('bg-gray-600', 'hover:bg-gray-700');
        }, 2000);
    });
}
function copyAllLinks() {
    const links = Array.from(document.querySelectorAll('a[href*="api.whatsapp.com"]'))
        .map(a => a.href).join('\n');
    navigator.clipboard.writeText(links).then(function() {
        alert('تم نسخ جميع الروابط');
    });
}
</script>
@endpush
@endsection