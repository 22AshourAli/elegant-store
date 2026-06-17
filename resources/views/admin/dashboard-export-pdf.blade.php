<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>{{ $arabic->utf8Glyphs(__('global.admin_financial_report'), 50, false) }}</title>
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
    <h1>{{ $arabic->utf8Glyphs(__('global.admin_financial_report'), 50, false) }}</h1>
    <p class="date">{{ now()->format('Y-m-d') }}</p>

    <table>
        <tr><th>{{ $arabic->utf8Glyphs(__('global.admin_statement'), 50, false) }}</th><th>{{ $arabic->utf8Glyphs(__('global.admin_value'), 50, false) }}</th></tr>
        <tr><td>{{ $arabic->utf8Glyphs(__('global.admin_total_orders'), 50, false) }}</td><td>{{ $totalOrders }}</td></tr>
        <tr><td>{{ $arabic->utf8Glyphs(__('global.admin_online_orders'), 50, false) }}</td><td>{{ $onlineOrders }}</td></tr>
        <tr><td>{{ $arabic->utf8Glyphs(__('global.admin_offline_orders'), 50, false) }}</td><td>{{ $offlineOrders }}</td></tr>
        <tr><td>{{ $arabic->utf8Glyphs(__('global.admin_product_revenue'), 50, false) }}</td><td>{{ number_format((int) round($totalProductRevenue)) }} {{ __('global.currency') }}</td></tr>
        <tr><td>{{ $arabic->utf8Glyphs(__('global.admin_shipping_collected'), 50, false) }}</td><td>{{ number_format((int) round($totalShippingCollected)) }} {{ __('global.currency') }}</td></tr>
        <tr><td>{{ $arabic->utf8Glyphs(__('global.admin_cost_of_goods'), 50, false) }}</td><td>{{ number_format((int) round($totalCosts)) }} {{ __('global.currency') }}</td></tr>
        <tr><td>{{ $arabic->utf8Glyphs(__('global.admin_other_expenses'), 50, false) }}</td><td>{{ number_format((int) round($totalManualExpenses)) }} {{ __('global.currency') }}</td></tr>
        <tr class="total">
            <td>{{ $arabic->utf8Glyphs(__('global.admin_net_profit'), 50, false) }}</td>
            <td class="{{ $netProfit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                {{ number_format((int) round($netProfit)) }} {{ __('global.currency') }}
            </td>
        </tr>
        <tr><td>{{ $arabic->utf8Glyphs(__('global.admin_profit_margin'), 50, false) }}</td><td>{{ $profitMargin }}%</td></tr>
        <tr><td>{{ $arabic->utf8Glyphs(__('global.admin_aov'), 50, false) }}</td><td>{{ number_format((int) round($aov)) }} {{ __('global.currency') }}</td></tr>
    </table>

    @if(isset($lowStockItems) && $lowStockItems->count() > 0)
    <div class="section-title">{{ $arabic->utf8Glyphs(__('global.admin_low_stock_alert'), 50, false) }}</div>
    <table>
        <tr><th>{{ $arabic->utf8Glyphs(__('global.product'), 50, false) }}</th><th>{{ $arabic->utf8Glyphs(__('global.admin_branch'), 50, false) }}</th><th>{{ $arabic->utf8Glyphs(__('global.admin_stock'), 50, false) }}</th></tr>
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