@extends('admin.layouts.app')
@section('page-title', 'إرسال رسالة إلى ' . $customer->name)
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Customer info --}}
        <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
            <h3 class="font-bold mb-4">معلومات العميل</h3>
            <div class="space-y-3 text-sm">
                <div><span class="font-semibold">الاسم:</span> {{ $customer->name }}</div>
                <div><span class="font-semibold">البريد:</span> {{ $customer->email ?? 'غير متوفر' }}</div>
                <div><span class="font-semibold">الهاتف:</span> {{ $customer->phone ?? 'غير متوفر' }}</div>
                <div><span class="font-semibold">إجمالي الطلبات:</span> {{ $totalOrders }}</div>
                <div><span class="font-semibold">إجمالي الإنفاق:</span> {{ number_format($totalSpent) }} ج.م</div>
                @php $isOnline = !empty($customer->email); @endphp
                <div>
                    <span class="font-semibold">النوع:</span>
                    @if($isOnline)
                        <span class="px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-700">أونلاين</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-700">أوفلاين</span>
                    @endif
                    @if($totalOrders > 0)
                        <span class="px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-700 mr-1">سبق له الشراء</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-500 mr-1">جديد</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Send message card with Alpine.js --}}
        <div class="bg-white dark:bg-gray-800 rounded shadow p-6"
             x-data="{
                 message: '',
                 sending: false,
                 lastLink: '',
                 toast: { show: false, message: '', type: 'success' },
                 phone: '{{ $waPhone }}',
                 waLink: '{{ $waLink }}',

                 useTemplate(type) {
                     const name = '{{ $customer->name }}';
                     const templates = {
                         marketing: 'مرحباً ' + name + '،\n\nنقدم لكم أحدث تشكيلاتنا من Elegant Store!\n\nتصفحوا مجموعتنا الجديدة واستمتعوا بتجربة تسوق استثنائية.\n\nزيارتكم تسعدنا \uD83D\uDED2',
                         followup: 'مرحباً ' + name + '،\n\nنود متابعة طلبكم الأخير. نأمل أن تكونوا راضين عن مشترياتكم.\n\nإذا كان لديكم أي استفسار، نحن هنا لخدمتكم.',
                         discount: 'عرض خاص لكم ' + name + '! \uD83C\uDF81\n\nكود خصم بقيمة 10% على مشترياتكم القادمة.\n\nاستخدموا الكود: WELCOME10\n\nصالح لمدة 3 أيام فقط!'
                     };
                     this.message = templates[type] || '';
                 },

                 send() {
                     if (!this.message.trim()) return;
                     this.sending = true;

                     // Build WA link with the message
                     const url = this.waLink + '&text=' + encodeURIComponent(this.message);

                     // Log in background (fetch ignores errors)
                     fetch('{{ route('admin.whatsapp.send', ['user' => $customer->id]) }}', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': document.querySelector('input[name=\'_token\']')?.value || '',
                             'Accept': 'application/json',
                         },
                         body: JSON.stringify({ message: this.message }),
                     }).catch(() => {});

                     // Open WhatsApp in new tab
                     window.open(url, '_blank');
                     this.lastLink = url;
                     this.sending = false;
                     this.showToast('تم فتح واتساب في تبويب جديد', 'success');
                     this.message = '';
                 },

                 copyLink() {
                     if (!this.lastLink) {
                         this.showToast('يرجى إرسال الرسالة أولاً', 'error');
                         return;
                     }
                     navigator.clipboard.writeText(this.lastLink);
                     this.showToast('تم نسخ الرابط', 'success');
                 },

                 showToast(msg, type) {
                     this.toast = { show: true, message: msg, type };
                     setTimeout(() => { this.toast.show = false; }, 4000);
                 }
             }">

            <h3 class="font-bold mb-4">إرسال رسالة واتساب</h3>

            {{-- Templates --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">قوالب جاهزة</label>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="useTemplate('marketing')"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950 transition">
                        عرض تسويقي
                    </button>
                    <button type="button" @click="useTemplate('followup')"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-950 transition">
                        متابعة طلب
                    </button>
                    <button type="button" @click="useTemplate('discount')"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 hover:bg-green-50 dark:hover:bg-green-950 transition">
                        كود خصم
                    </button>
                </div>
            </div>

            <form @submit.prevent="send">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">نص الرسالة</label>
                    <textarea x-model="message" rows="5" required
                        class="w-full border rounded px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600"
                        placeholder="اكتب رسالتك هنا..."></textarea>
                </div>

                <div class="flex gap-2">
                    <button type="submit" :disabled="sending"
                            class="flex-1 bg-green-600 text-white font-bold py-2.5 rounded-lg hover:bg-green-700 transition text-sm flex items-center justify-center gap-2 disabled:opacity-50">
                        <svg x-show="!sending" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        <svg x-show="sending" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" style="display:none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="sending ? 'جاري الإرسال...' : 'فتح واتساب وإرسال'"></span>
                    </button>

                    <button type="button" @click="copyLink"
                            class="px-4 py-2.5 bg-gray-600 text-white font-bold rounded-lg hover:bg-gray-700 transition text-sm">
                        نسخ الرابط
                    </button>
                </div>
            </form>

            {{-- Toast --}}
            <div x-show="toast.show" x-transition
                 x-text="toast.message"
                 :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'"
                 class="mt-4 text-white text-sm text-center py-2 px-4 rounded-lg"
                 style="display: none">
            </div>
        </div>

        {{-- Logs --}}
        <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
            <h3 class="font-bold mb-4">سجل الرسائل السابقة</h3>
            @if($logs->count())
                <div class="space-y-3">
                    @foreach($logs as $log)
                        <div class="p-3 border dark:border-gray-700 rounded-lg {{ $log->status === 'sent' ? 'bg-green-50 dark:bg-green-950/20' : 'bg-amber-50 dark:bg-amber-950/20' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold {{ $log->status === 'sent' ? 'text-green-700' : 'text-amber-700' }}">
                                    {{ $log->status === 'sent' ? 'مرسلة' : 'معلقة' }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ $log->sent_at?->format('Y-m-d H:i') }}
                                    @if($log->sentBy) | بواسطة {{ $log->sentBy->name }} @endif
                                </span>
                            </div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $log->message }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400 text-sm">لا توجد رسائل سابقة</p>
            @endif
        </div>
    </div>

    {{-- Sidebar --}}
    <div>
        <div class="bg-white dark:bg-gray-800 rounded shadow p-6 space-y-4">
            <h3 class="font-bold">إجراء سريع</h3>
            <p class="text-xs text-gray-500">سجل أن الرسالة قد أُرسلت دون فتح واتساب</p>
            <form action="{{ route('admin.whatsapp.mark-sent', ['user' => $customer->id]) }}" method="POST">
                @csrf
                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2.5 rounded-lg hover:bg-indigo-700 transition text-sm">
                    تسجيل كمرسلة
                </button>
            </form>
            <a href="{{ route('admin.whatsapp.next') }}" class="block w-full text-center bg-gray-600 text-white font-bold py-2.5 rounded-lg hover:bg-gray-700 transition text-sm">
                التالي في الخط
            </a>
        </div>
    </div>
</div>
@endsection