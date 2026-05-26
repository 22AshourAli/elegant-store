<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فاتورة #{{ $order->id }} | Elegant Store</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Segoe UI', Tahoma, sans-serif;
            background: #fff;
            color: #1f2937;
            font-size: 13px;
            line-height: 1.6;
            padding: 30px;
        }
        .no-print { text-align: center; margin-bottom: 20px; }
        .no-print button {
            background: #4f46e5; color: #fff; border: none;
            padding: 10px 28px; border-radius: 8px; font-size: 14px;
            font-weight: 600; cursor: pointer;
        }
        .no-print button:hover { background: #4338ca; }
        .invoice {
            max-width: 800px; margin: 0 auto;
            background: #fff; border: 1px solid #e5e7eb;
            border-radius: 12px; padding: 40px;
        }
        .header {
            display: flex; justify-content: space-between; align-items: flex-start;
            padding-bottom: 24px; border-bottom: 2px solid #4f46e5;
            margin-bottom: 24px;
        }
        .header .logo { display: flex; align-items: center; gap: 10px; }
        .header .logo svg { width: 40px; height: 40px; }
        .header .logo-text { display: flex; flex-direction: column; align-items: flex-start; }
        .header .logo-text .brand { font-size: 22px; font-weight: 900; letter-spacing: -0.5px; line-height: 1.1; color: #111827; }
        .header .logo-text .sub { font-size: 9px; font-weight: 700; letter-spacing: 3px; color: #4f46e5; }
        .header .title-area { text-align: right; }
        .header .title-area h1 { font-size: 20px; font-weight: 800; color: #4f46e5; }
        .header .title-area p { font-size: 12px; color: #6b7280; margin-top: 2px; }
        .info-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 24px;
            margin-bottom: 24px;
        }
        .info-box {
            background: #f9fafb; border-radius: 10px; padding: 16px;
            border: 1px solid #f3f4f6;
        }
        .info-box h3 {
            font-size: 13px; font-weight: 700; color: #4f46e5;
            margin-bottom: 10px; padding-bottom: 6px;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-box p { font-size: 13px; margin-bottom: 3px; color: #374151; }
        .info-box .label { color: #9ca3af; font-size: 11px; }
        .info-box .value { font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        thead th {
            background: #4f46e5; color: #fff; font-size: 12px;
            font-weight: 700; text-align: right;
            padding: 10px 12px; white-space: nowrap;
        }
        thead th:first-child { border-radius: 0 8px 8px 0; }
        thead th:last-child { border-radius: 8px 0 0 8px; }
        tbody td {
            padding: 10px 12px; border-bottom: 1px solid #f3f4f6;
            font-size: 13px; vertical-align: middle;
        }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #f9fafb; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .summary {
            margin-bottom: 24px; padding: 16px 20px;
            background: #f9fafb; border-radius: 10px;
            border: 1px solid #f3f4f6;
        }
        .summary .row {
            display: flex; justify-content: space-between; padding: 5px 0;
            font-size: 13px;
        }
        .summary .row.total {
            border-top: 2px solid #4f46e5; margin-top: 6px; padding-top: 10px;
            font-size: 16px; font-weight: 800; color: #4f46e5;
        }
        .footer {
            text-align: center; padding-top: 20px;
            border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 12px;
        }
        .footer .thanks { font-size: 15px; font-weight: 700; color: #4f46e5; margin-bottom: 6px; }
        .footer .contact { margin-top: 6px; }
        .footer .contact span { display: inline-block; margin: 0 10px; }
        @media print {
            body { padding: 0; background: #fff; }
            .no-print { display: none !important; }
            .invoice { border: none; border-radius: 0; padding: 30px; box-shadow: none; }
            thead th { background: #4f46e5 !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .info-box { background: #f9fafb !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .summary { background: #f9fafb !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { margin: 15mm; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print">
        <button onclick="window.print()">طباعة الفاتورة</button>
    </div>

    <div class="invoice">
        <!-- الهيدر: اللوجو + عنوان الفاتورة -->
        <div class="header">
            <div class="title-area">
                <h1>فاتورة ضريبية</h1>
                <p>رقم الفاتورة: {{ $order->id }}</p>
                <p>تاريخ الفاتورة: {{ $order->created_at->format('Y/m/d') }}</p>
            </div>
            <div class="logo">
                <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 2L30 16L16 30L2 16L16 2Z" fill="#4f46e5"/>
                    <path d="M16 8L24 16L16 24L8 16L16 8Z" fill="#fff"/>
                    <path d="M16 13L19 16L16 19L13 16L16 13Z" fill="#4f46e5"/>
                </svg>
                <div class="logo-text">
                    <span class="brand">ELEGANT</span>
                    <span class="sub">STORE</span>
                </div>
            </div>
        </div>

        <!-- معلومات العميل + الدفع -->
        <div class="info-grid">
            <div class="info-box">
                <h3>معلومات العميل</h3>
                <p><span class="label">الاسم:</span> <span class="value">{{ $order->user->name }}</span></p>
                <p><span class="label">رقم الهاتف:</span> <span class="value">{{ $order->user->phone ?? '—' }}</span></p>
                <p><span class="label">البريد الإلكتروني:</span> <span class="value">{{ $order->user->email }}</span></p>
                <p style="margin-top:6px"><span class="label">عنوان الشحن:</span></p>
                <p class="value">{{ $order->shipping_address }}</p>
                @if($order->notes)
                    <p style="margin-top:6px"><span class="label">ملاحظات العميل:</span></p>
                    <p class="value" style="font-style:italic">"{{ $order->notes }}"</p>
                @endif
            </div>
            <div class="info-box">
                <h3>بيانات الدفع</h3>
                <p><span class="label">طريقة الدفع:</span>
                    <span class="value">
                        @if($order->payment_method === 'cash') الدفع عند الاستلام
                        @elseif($order->payment_method === 'card') بطاقة بنكية
                        @else محفظة إلكترونية @endif
                    </span>
                </p>
                <p><span class="label">حالة الدفع:</span>
                    <span class="value">
                        @if($order->payment_status === 'paid') مدفوع
                        @elseif($order->payment_status === 'unpaid') غير مدفوع
                        @else فشل الدفع @endif
                    </span>
                </p>
                <p><span class="label">رقم العملية:</span> <span class="value">{{ $order->payment->transaction_id ?? '—' }}</span></p>
            </div>
        </div>

        <!-- جدول المنتجات -->
        <h3 style="font-size:14px;font-weight:700;color:#4f46e5;margin-bottom:10px">المنتجات المطلوبة</h3>
        <table>
            <thead>
                <tr>
                    <th style="width:40%">المنتج</th>
                    <th>اللون/المقاس</th>
                    <th class="text-center">الكمية</th>
                    <th class="text-left">سعر الوحدة</th>
                    <th class="text-left">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td class="value">{{ $item->product_name }}</td>
                    <td style="color:#6b7280;font-size:12px">
                        @if($item->color) اللون: {{ $item->color }} @endif
                        @if($item->color && $item->size) | @endif
                        @if($item->size) المقاس: {{ $item->size }} @endif
                        @if(!$item->color && !$item->size) — @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-left">{{ (int) round($item->unit_price) }} ج.م</td>
                    <td class="text-left value">{{ (int) round($item->total) }} ج.م</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- ملخص الفاتورة -->
        <div class="summary">
            <div class="row">
                <span>مجموع المنتجات</span>
                <span>{{ (int) round($order->items->sum('total')) }} ج.م</span>
            </div>
            @if($order->discount && $order->discount > 0)
            <div class="row">
                <span>الخصم</span>
                <span>-{{ (int) round($order->discount) }} ج.م</span>
            </div>
            @endif
            <div class="row">
                <span>الشحن</span>
                <span>@if($order->shipping_cost && $order->shipping_cost > 0) {{ (int) round($order->shipping_cost) }} ج.م @else مجاني @endif</span>
            </div>
            <div class="row total">
                <span>الإجمالي النهائي</span>
                <span>{{ (int) round($order->total) }} ج.م</span>
            </div>
        </div>

        <!-- الفوتر -->
        <div class="footer">
            <div class="thanks">شكراً لتسوقك مع Elegant Store!</div>
            <div class="contact">
                <span>Elegant Store</span>
                <span>الهاتف: +{{ config('store.admin_phone') }}</span>
                <span>البريد: {{ config('store.admin_email') }}</span>
            </div>
        </div>
    </div>
</body>
</html>
