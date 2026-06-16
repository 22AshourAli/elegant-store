<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>{{ $arabic->utf8Glyphs('التقرير المالي', 50, false) }}</title>
    <style>
        @page { margin: 20mm 15mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 13px; color: #1f2937; direction: rtl; unicode-bidi: embed; }
        h1 { text-align: center; font-size: 20px; margin-bottom: 5px; }
        .date { text-align: center; color: #6b7280; font-size: 11px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px 12px; text-align: right; border-bottom: 1px solid #e5e7eb; }
        th { background: #f3f4f6; font-weight: bold; font-size: 11px; }
        .total { font-weight: bold; font-size: 14px; }
        .profit-positive { color: #059669; }
        .profit-negative { color: #dc2626; }
        .section-title { font-size: 14px; font-weight: bold; margin: 15px 0 8px; }
    </style>
</head>
<body>
    <h1>{{ $arabic->utf8Glyphs('التقرير المالي', 50, false) }}</h1>
    <p class="date">{{ now()->format('Y-m-d') }}</p>

    <table>
        <tr><th>{{ $arabic->utf8Glyphs('البيان', 50, false) }}</th><th>{{ $arabic->utf8Glyphs('القيمة', 50, false) }}</th></tr>
        <tr><td>{{ $arabic->utf8Glyphs('إجمالي الطلبات', 50, false) }}</td><td>{{ $totalOrders }}</td></tr>
        <tr><td>{{ $arabic->utf8Glyphs('طلبات Online', 50, false) }}</td><td>{{ $onlineOrders }}</td></tr>
        <tr><td>{{ $arabic->utf8Glyphs('طلبات Offline', 50, false) }}</td><td>{{ $offlineOrders }}</td></tr>
        <tr><td>{{ $arabic->utf8Glyphs('إيراد المنتجات', 50, false) }}</td><td>{{ number_format((int) round($totalProductRevenue)) }} EGP</td></tr>
        <tr><td>{{ $arabic->utf8Glyphs('شحن محصل', 50, false) }}</td><td>{{ number_format((int) round($totalShippingCollected)) }} EGP</td></tr>
        <tr><td>{{ $arabic->utf8Glyphs('تكلفة البضاعة', 50, false) }}</td><td>{{ number_format((int) round($totalCosts)) }} EGP</td></tr>
        <tr><td>{{ $arabic->utf8Glyphs('مصروفات أخرى', 50, false) }}</td><td>{{ number_format((int) round($totalManualExpenses)) }} EGP</td></tr>
        <tr class="total">
            <td>{{ $arabic->utf8Glyphs('صافي الربح', 50, false) }}</td>
            <td class="{{ $netProfit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                {{ number_format((int) round($netProfit)) }} EGP
            </td>
        </tr>
        <tr><td>{{ $arabic->utf8Glyphs('هامش الربح', 50, false) }}</td><td>{{ $profitMargin }}%</td></tr>
        <tr><td>{{ $arabic->utf8Glyphs('متوسط قيمة الطلب', 50, false) }}</td><td>{{ number_format((int) round($aov)) }} EGP</td></tr>
    </table>

    @if(isset($lowStockItems) && $lowStockItems->count() > 0)
    <div class="section-title">{{ $arabic->utf8Glyphs('تنبيه المخزون المنخفض', 50, false) }}</div>
    <table>
        <tr><th>{{ $arabic->utf8Glyphs('المنتج', 50, false) }}</th><th>{{ $arabic->utf8Glyphs('الفرع', 50, false) }}</th><th>{{ $arabic->utf8Glyphs('المخزون', 50, false) }}</th></tr>
        @foreach($lowStockItems as $item)
        <tr>
            <td>{{ $item->product_name }} @if($item->sku)({{ $item->sku }})@endif</td>
            <td>{{ $item->branch_name }}</td>
            <td>{{ $item->stock }}</td>
        </tr>
        @endforeach
    </table>
    @endif
</body>
</html>