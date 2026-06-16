<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>التقرير المالي</title>
    <style>
        @page { margin: 20mm 15mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1f2937; }
        h1 { text-align: center; font-size: 20px; margin-bottom: 5px; }
        .date { text-align: center; color: #6b7280; font-size: 11px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px 12px; text-align: right; border-bottom: 1px solid #e5e7eb; }
        th { background: #f3f4f6; font-weight: bold; font-size: 11px; }
        .total { font-weight: bold; font-size: 14px; }
        .profit-positive { color: #059669; }
        .profit-negative { color: #dc2626; }
        .section-title { font-size: 14px; font-weight: bold; margin: 15px 0 8px; }
        .grid-2 { display: flex; gap: 20px; }
        .grid-2 > div { flex: 1; }
    </style>
</head>
<body>
    <h1>التقرير المالي</h1>
    <p class="date">{{ now()->format('Y-m-d') }}</p>

    <table>
        <tr><th>البيان</th><th>القيمة</th></tr>
        <tr><td>إجمالي الطلبات</td><td>{{ $totalOrders }}</td></tr>
        <tr><td>طلبات Online</td><td>{{ $onlineOrders }}</td></tr>
        <tr><td>طلبات Offline</td><td>{{ $offlineOrders }}</td></tr>
        <tr><td>إيراد المنتجات</td><td>{{ number_format((int) round($totalProductRevenue)) }} EGP</td></tr>
        <tr><td>شحن محصل</td><td>{{ number_format((int) round($totalShippingCollected)) }} EGP</td></tr>
        <tr><td>تكلفة البضاعة</td><td>{{ number_format((int) round($totalCosts)) }} EGP</td></tr>
        <tr><td>مصروفات أخرى</td><td>{{ number_format((int) round($totalManualExpenses)) }} EGP</td></tr>
        <tr class="total">
            <td>صافي الربح</td>
            <td class="{{ $netProfit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                {{ number_format((int) round($netProfit)) }} EGP
            </td>
        </tr>
        <tr><td>هامش الربح</td><td>{{ $profitMargin }}%</td></tr>
        <tr><td>متوسط قيمة الطلب</td><td>{{ number_format((int) round($aov)) }} EGP</td></tr>
    </table>

    @if(isset($lowStockItems) && $lowStockItems->count() > 0)
    <div class="section-title">تنبيه المخزون المنخفض</div>
    <table>
        <tr><th>المنتج</th><th>الفرع</th><th>المخزون</th></tr>
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
