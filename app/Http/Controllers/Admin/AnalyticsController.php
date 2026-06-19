<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\OrderStatus;
use App\Models\AbandonedCart;
use App\Models\Expense;
use App\Models\Order;
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
        $dates = $this->parsePeriod($period, $request);

        $completedStatuses = [
            OrderStatus::Confirmed->value,
            OrderStatus::Delivered->value,
            OrderStatus::Collected->value,
        ];
        $excludedStatuses = [
            OrderStatus::Cancelled->value,
            OrderStatus::Returned->value,
        ];
        $activeStatuses = array_merge($completedStatuses, [
            OrderStatus::Pending->value,
            OrderStatus::Processing->value,
            OrderStatus::Shipped->value,
            OrderStatus::OutForDelivery->value,
        ]);

        // --- Base order scopes ---
        $baseQuery = Order::query();
        $activeQuery = Order::whereNotIn('status', $excludedStatuses);
        $completedQuery = Order::whereIn('status', $completedStatuses);
        if ($dates) {
            $baseQuery->whereBetween('created_at', [$dates['from'], $dates['to']]);
            $activeQuery->whereBetween('created_at', [$dates['from'], $dates['to']]);
            $completedQuery->whereBetween('created_at', [$dates['from'], $dates['to']]);
        }

        // --- Counts with DISTINCT to prevent item-join duplication ---
        $totalOrders = (clone $baseQuery)->count();
        $completedOrders = (clone $completedQuery)->count();
        $onlineOrders = (clone $baseQuery)->where('order_type', 'online')->count();
        $offlineOrders = (clone $baseQuery)->where('order_type', 'offline')->count();

        // --- Financial aggregates (completed orders only) ---
        $totalSales = (float) (clone $completedQuery)->sum('total');
        $totalProductRevenue = (float) (clone $completedQuery)->sum('subtotal');
        $totalShippingCollected = (float) (clone $completedQuery)->sum('shipping_cost');

        // --- COGS: cost_price * quantity via order_items ---
        $cogsQuery = DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', $completedStatuses)
            ->whereNotNull('product_variants.cost_price');
        if ($dates) {
            $cogsQuery->whereBetween('orders.created_at', [$dates['from'], $dates['to']]);
        }
        $totalCosts = (float) ($cogsQuery->selectRaw('COALESCE(SUM(order_items.quantity * product_variants.cost_price), 0) as total')->value('total') ?? 0);

        // --- Manual expenses ---
        $expensesQuery = Expense::query();
        if ($dates) {
            $expensesQuery->whereBetween('expense_date', [$dates['from'], $dates['to']]);
        }
        $totalManualExpenses = (float) (clone $expensesQuery)->sum('amount');

        // --- Net profit ---
        $totalExpenses = $totalManualExpenses + $totalShippingCollected;
        $netProfit = $totalProductRevenue - $totalCosts - $totalExpenses;
        $profitMargin = $totalProductRevenue > 0 ? round(($netProfit / $totalProductRevenue) * 100, 1) : 0;

        // --- Conversion rate ---
        $conversionRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0;

        // --- AOV ---
        $aov = $completedOrders > 0 ? round($totalSales / $completedOrders, 2) : 0;

        // --- Sales trend (daily) ---
        $trendDays = match ($period) {
            'today' => 0, 'week' => 6, 'month' => 29, 'quarter' => 89, 'year' => 364, default => 29
        };
        $chartDaily = $trendDays > 0;

        if ($chartDaily) {
            $dailyQuery = DB::table('orders')
                ->whereIn('status', $completedStatuses)
                ->where('created_at', '>=', now()->subDays($trendDays)->startOfDay());
            $dailySales = (clone $dailyQuery)
                ->selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(DISTINCT id) as count')
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();

            $chartData = [];
            for ($i = $trendDays; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $chartData[$date] = ['revenue' => 0, 'count' => 0];
            }
            foreach ($dailySales as $sale) {
                if (isset($chartData[$sale->date])) {
                    $chartData[$sale->date] = [
                        'revenue' => (float) $sale->revenue,
                        'count' => (int) $sale->count,
                    ];
                }
            }
            $chartLabels = collect($chartData)->map(fn($d, $k) => \Carbon\Carbon::parse($k)->format('d M'))->values();
            $chartValues = collect($chartData)->pluck('revenue')->values();
        } else {
            $chartLabels = collect([]);
            $chartValues = collect([]);
        }

        // --- Top products ---
        $topProductsQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', $completedStatuses);
        if ($dates) {
            $topProductsQuery->whereBetween('orders.created_at', [$dates['from'], $dates['to']]);
        }
        $topProducts = (clone $topProductsQuery)
            ->selectRaw('order_items.product_name, SUM(order_items.total) as total, SUM(order_items.quantity) as quantity')
            ->groupBy('order_items.product_name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // --- Colors & sizes ---
        $colorQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', $completedStatuses)
            ->whereNotNull('order_items.color');
        if ($dates) {
            $colorQuery->whereBetween('orders.created_at', [$dates['from'], $dates['to']]);
        }
        $topColors = (clone $colorQuery)
            ->selectRaw('order_items.color, SUM(order_items.quantity) as quantity')
            ->groupBy('order_items.color')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get();

        $sizeQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', $completedStatuses)
            ->whereNotNull('order_items.size');
        if ($dates) {
            $sizeQuery->whereBetween('orders.created_at', [$dates['from'], $dates['to']]);
        }
        $topSizes = (clone $sizeQuery)
            ->selectRaw('order_items.size, SUM(order_items.quantity) as quantity')
            ->groupBy('order_items.size')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get();

        // --- Abandoned carts ---
        $abandonedQuery = AbandonedCart::where('status', 'abandoned');
        if ($dates) {
            $abandonedQuery->whereBetween('first_abandoned_at', [$dates['from'], $dates['to']]);
        }
        $totalAbandoned = (clone $abandonedQuery)->count();
        $totalAbandonedValue = (clone $abandonedQuery)->sum('total');

        $recoveredQuery = AbandonedCart::where('status', 'recovered');
        if ($dates) {
            $recoveredQuery->whereBetween('updated_at', [$dates['from'], $dates['to']]);
        }
        $recoveredCarts = (clone $recoveredQuery)->count();

        $kpi = compact(
            'totalSales', 'netProfit', 'profitMargin',
            'completedOrders', 'totalOrders', 'conversionRate',
            'onlineOrders', 'offlineOrders', 'aov',
            'totalProductRevenue', 'totalShippingCollected', 'totalCosts', 'totalManualExpenses',
        );

        $abandoned = [
            'total' => $totalAbandoned,
            'total_value' => $totalAbandonedValue,
            'recovered' => $recoveredCarts,
        ];

        return view('admin.reports.index', compact(
            'period', 'kpi',
            'chartLabels', 'chartValues',
            'topProducts', 'topColors', 'topSizes',
            'abandoned',
        ));
    }

    public function exportCsv(Request $request)
    {
        $period = $request->input('period', 'month');
        $dates = $this->parsePeriod($period, $request);

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
        if ($dates) {
            $query->whereBetween('orders.created_at', [$dates['from'], $dates['to']]);
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

    private function parsePeriod(string $period, Request $request): ?array
    {
        return match ($period) {
            'today' => ['from' => now()->startOfDay(), 'to' => now()->endOfDay()],
            'week' => ['from' => now()->startOfWeek(), 'to' => now()->endOfDay()],
            'month' => ['from' => now()->startOfMonth(), 'to' => now()->endOfMonth()],
            'quarter' => ['from' => now()->subMonths(2)->startOfMonth(), 'to' => now()->endOfMonth()],
            'year' => ['from' => now()->startOfYear(), 'to' => now()->endOfYear()],
            'all' => null,
            default => $request->filled(['from', 'to'])
                ? ['from' => $request->date('from')->startOfDay(), 'to' => $request->date('to')->endOfDay()]
                : ['from' => now()->startOfMonth(), 'to' => now()->endOfMonth()],
        };
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
