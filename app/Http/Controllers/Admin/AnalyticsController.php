<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\OrderStatus;
use App\Models\AbandonedCart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\AnalyticsService;
use App\Services\PaymentReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analytics,
        private readonly PaymentReconciliationService $paymentService
    ) {}

    public function index(Request $request)
    {
        $period = $request->input('period', 'month');
        $dateFrom = match ($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            'all' => null,
            default => now()->startOfMonth(),
        };

        $completedStatuses = [
            OrderStatus::Confirmed->value,
            OrderStatus::Delivered->value,
            OrderStatus::Collected->value,
        ];

        // Base order query
        $ordersQuery = Order::query();
        if ($dateFrom) {
            $ordersQuery->where('created_at', '>=', $dateFrom);
        }

        // Completed orders query
        $completedQuery = clone $ordersQuery;
        $completedQuery->whereIn('status', $completedStatuses);

        // Total completed orders
        $completedOrders = (clone $completedQuery)->count();

        // Total sales (sum of total for completed orders)
        $totalSales = (clone $completedQuery)->sum('total');

        // Total orders (all statuses)
        $totalOrders = (clone $ordersQuery)->count();

        // Conversion rate
        $conversionRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0;

        // Net profit
        $profitQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->whereIn('orders.status', $completedStatuses);
        if ($dateFrom) {
            $profitQuery->where('orders.created_at', '>=', $dateFrom);
        }
        $netProfit = $profitQuery
            ->selectRaw('COALESCE(SUM((order_items.unit_price - COALESCE(product_variants.cost_price, 0)) * order_items.quantity), 0) as profit')
            ->value('profit');
        $netProfit = round((float) $netProfit, 2);

        // Daily/monthly sales trend
        $trendQuery = DB::table('orders')
            ->whereIn('status', $completedStatuses);
        if ($dateFrom) {
            $trendQuery->where('created_at', '>=', $dateFrom);
        }
        $salesTrend = $trendQuery
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();
        $salesTrendLabels = $salesTrend->pluck('date')->map(fn($d) => (string) $d);
        $salesTrendData = $salesTrend->pluck('total')->map(fn($v) => (float) $v);

        // Top products by revenue
        $topProductsQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', $completedStatuses);
        if ($dateFrom) {
            $topProductsQuery->where('orders.created_at', '>=', $dateFrom);
        }
        $topProducts = $topProductsQuery
            ->selectRaw('order_items.product_name, SUM(order_items.total) as total, SUM(order_items.quantity) as quantity')
            ->groupBy('order_items.product_name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
        $topProductLabels = $topProducts->pluck('product_name');
        $topProductData = $topProducts->pluck('total')->map(fn($v) => (float) $v);

        // Top colors
        $topColorsQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', $completedStatuses)
            ->whereNotNull('order_items.color');
        if ($dateFrom) {
            $topColorsQuery->where('orders.created_at', '>=', $dateFrom);
        }
        $topColors = $topColorsQuery
            ->selectRaw('order_items.color, SUM(order_items.quantity) as quantity')
            ->groupBy('order_items.color')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get();

        // Top sizes
        $topSizesQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', $completedStatuses)
            ->whereNotNull('order_items.size');
        if ($dateFrom) {
            $topSizesQuery->where('orders.created_at', '>=', $dateFrom);
        }
        $topSizes = $topSizesQuery
            ->selectRaw('order_items.size, SUM(order_items.quantity) as quantity')
            ->groupBy('order_items.size')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get();

        // Abandoned carts
        $abandonedQuery = AbandonedCart::where('status', 'abandoned');
        if ($dateFrom) {
            $abandonedQuery->where('first_abandoned_at', '>=', $dateFrom);
        }
        $totalAbandoned = (clone $abandonedQuery)->count();
        $totalAbandonedValue = (clone $abandonedQuery)->sum('total');

        // Recovered carts
        $recoveredQuery = AbandonedCart::where('status', 'recovered');
        if ($dateFrom) {
            $recoveredQuery->where('updated_at', '>=', $dateFrom);
        }
        $recoveredCarts = (clone $recoveredQuery)->count();

        $kpi = [
            'total_sales' => $totalSales,
            'net_profit' => $netProfit,
            'completed_orders' => $completedOrders,
            'conversion_rate' => $conversionRate,
        ];

        $abandoned = [
            'total' => $totalAbandoned,
            'total_value' => $totalAbandonedValue,
            'recovered' => $recoveredCarts,
        ];

        return view('admin.reports.index', compact(
            'period',
            'kpi',
            'salesTrendLabels',
            'salesTrendData',
            'topProductLabels',
            'topProductData',
            'topColors',
            'topSizes',
            'topProducts',
            'abandoned',
        ));
    }

    public function exportCsv(Request $request)
    {
        $period = $request->input('period', 'month');
        $dateFrom = match ($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            'all' => null,
            default => now()->startOfMonth(),
        };

        $completedStatuses = [
            OrderStatus::Confirmed->value,
            OrderStatus::Delivered->value,
            OrderStatus::Collected->value,
        ];

        $query = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->whereIn('orders.status', $completedStatuses)
            ->selectRaw("
                orders.id as order_id,
                orders.order_type,
                orders.created_at as order_date,
                orders.total as order_total,
                order_items.product_name,
                order_items.color,
                order_items.size,
                order_items.quantity,
                order_items.unit_price,
                order_items.total as item_total,
                COALESCE(product_variants.cost_price, 0) as cost_price,
                (order_items.unit_price - COALESCE(product_variants.cost_price, 0)) * order_items.quantity as profit
            ");
        if ($dateFrom) {
            $query->where('orders.created_at', '>=', $dateFrom);
        }
        $rows = $query->orderBy('orders.created_at')->get();

        $filename = 'elegant-store-report-' . now()->format('Y-m-d-H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, [
                'Order ID', 'Type', 'Date', 'Order Total',
                'Product', 'Color', 'Size', 'Qty',
                'Unit Price', 'Item Total', 'Cost Price', 'Profit'
            ]);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->order_id,
                    $row->order_type,
                    $row->order_date,
                    $row->order_total,
                    $row->product_name,
                    $row->color,
                    $row->size,
                    $row->quantity,
                    $row->unit_price,
                    $row->item_total,
                    $row->cost_price,
                    round((float) $row->profit, 2),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function returnAnalytics()
    {
        $data = $this->analytics->returnAnalytics();
        return view('admin.reports.returns', compact('data'));
    }

    public function aovAndClv()
    {
        $data = $this->analytics->aovAndClv();
        return view('admin.reports.aov-clv', compact('data'));
    }

    public function deadStock()
    {
        $data = $this->analytics->deadStockReport();
        return view('admin.reports.dead-stock', compact('data'));
    }

    public function cartFunnel()
    {
        $data = $this->analytics->abandonedCartFunnel();
        return view('admin.reports.cart-funnel', compact('data'));
    }

    public function paymentReconciliation()
    {
        $data = $this->paymentService->financialSummary();
        return view('admin.reports.payment-reconciliation', compact('data'));
    }
}
