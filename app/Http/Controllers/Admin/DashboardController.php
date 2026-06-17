<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\ExchangeStatus;
use App\Enums\ReturnRequestStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Exchange;
use App\Models\Expense;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DashboardExport;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getReportData($request);
        extract($data);
        return view('admin.dashboard', $data);
    }

    public function exportCsv(Request $request)
    {
        $data = $this->getReportData($request);
        $period = $request->get('period', 'month');
        $filename = 'تقرير-المبيعات-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel Arabic support
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['التقرير المالي', date('Y-m-d')]);
            fputcsv($handle, []);
            fputcsv($handle, ['البيان', 'القيمة']);
            fputcsv($handle, ['إجمالي الطلبات', $data['totalOrders']]);
            fputcsv($handle, ['طلبات Online', $data['onlineOrders']]);
            fputcsv($handle, ['طلبات Offline', $data['offlineOrders']]);
            fputcsv($handle, ['إيراد المنتجات', number_format((int) round($data['totalProductRevenue'])) . ' EGP']);
            fputcsv($handle, ['شحن محصل', number_format((int) round($data['totalShippingCollected'])) . ' EGP']);
            fputcsv($handle, ['تكلفة البضاعة', number_format((int) round($data['totalCosts'])) . ' EGP']);
            fputcsv($handle, ['مصروفات أخرى', number_format((int) round($data['totalManualExpenses'])) . ' EGP']);
            fputcsv($handle, ['صافي الربح', number_format((int) round($data['netProfit'])) . ' EGP']);
            fputcsv($handle, ['هامش الربح', $data['profitMargin'] . '%']);
            fputcsv($handle, ['متوسط قيمة الطلب', number_format((int) round($data['aov'])) . ' EGP']);

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getReportData($request);
        $period = $request->get('period', 'month');
        $filename = 'تقرير-المبيعات-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new DashboardExport($data), $filename);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getReportData($request);
        $glyphs = new \ArPHP\I18N\Arabic('Glyphs');
        $data['arabic'] = $glyphs;
        $items = $data['lowStockItems'];
        if ($items && $items->count()) {
            $data['lowStockItems'] = $items->map(function ($item) use ($glyphs) {
                $item->product_name = $glyphs->utf8Glyphs($item->product_name, 50, false);
                $item->branch_name = $glyphs->utf8Glyphs($item->branch_name, 50, false);
                return $item;
            });
        }
        $period = $request->get('period', 'month');
        $pdf = Pdf::loadView('admin.dashboard-export-pdf', $data);
        return $pdf->download('تقرير-المبيعات-' . now()->format('Y-m-d') . '.pdf');
    }

    private function getReportData(Request $request): array
    {
        $user = auth()->user();
        $branchId = $user->isManager() ? $user->branch_id : null;

        $period = $request->get('period', 'month');
        $dates = $this->parsePeriod($period, $request);

        $orderQuery = Order::query();
        $revenueQuery = Order::whereNotIn('status', [OrderStatus::Cancelled->value, OrderStatus::Returned->value]);
        $returnedQuery = Order::where('status', OrderStatus::Returned->value);

        if ($branchId) {
            $orderQuery->where('branch_id', $branchId);
            $revenueQuery->where('branch_id', $branchId);
            $returnedQuery->where('branch_id', $branchId);
        }

        if ($dates) {
            $orderQuery->whereBetween('created_at', [$dates['from'], $dates['to']]);
            $revenueQuery->whereBetween('created_at', [$dates['from'], $dates['to']]);
            $returnedQuery->whereBetween('created_at', [$dates['from'], $dates['to']]);
        }

        $totalOrders = (clone $orderQuery)->count();
        $onlineOrders = (clone $orderQuery)->where('order_type', OrderType::Online->value)->count();
        $offlineOrders = (clone $orderQuery)->where('order_type', OrderType::Offline->value)->count();
        $totalRevenue = (float) (clone $revenueQuery)->sum('total');
        $totalProductRevenue = (float) (clone $revenueQuery)->sum('subtotal');
        $totalShippingCollected = (float) (clone $revenueQuery)->sum('shipping_cost');
        $totalCustomers = User::where('role', UserRole::Customer->value)->count();
        $returnedAmount = (float) (clone $returnedQuery)->sum('total');
        $returnedCount = (clone $returnedQuery)->count();

        $threshold = config('store.low_stock_threshold', 5);
        $lowStockCount = ProductVariant::whereHas('branches', function ($q) use ($branchId, $threshold) {
            $q->where('stock', '>', 0)->where('stock', '<', $threshold);
            if ($branchId) $q->where('branch_id', $branchId);
        })->count();

        $returnRequestQuery = ReturnRequest::query();
        $exchangeQuery = Exchange::query();
        if ($branchId) {
            $returnRequestQuery->whereHas('order', fn($q) => $q->where('branch_id', $branchId));
            $exchangeQuery->whereHas('order', fn($q) => $q->where('branch_id', $branchId));
        }
        $returnRequestCount = (clone $returnRequestQuery)->count();
        $returnRequestPending = (clone $returnRequestQuery)->where('status', ReturnRequestStatus::Pending->value)->count();
        $exchangeCount = (clone $exchangeQuery)->count();
        $exchangePending = (clone $exchangeQuery)->where('status', ExchangeStatus::Pending->value)->count();

        // --- Chart Data ---
        $chartDays = match ($period) { 'today' => 0, '7days' => 6, 'month' => 29, 'quarter' => 89, 'year' => 364, default => 29 };
        $chartMonths = match ($period) { 'today' => 0, '7days' => 0, 'month' => 0, 'quarter' => 2, 'year' => 11, default => 11 };
        $chartDaily = $chartDays > 0;

        if ($chartDaily) {
            $dailyQuery = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN status NOT IN ("' . OrderStatus::Cancelled->value . '","' . OrderStatus::Returned->value . '") THEN subtotal ELSE 0 END) - SUM(CASE WHEN status NOT IN ("' . OrderStatus::Cancelled->value . '","' . OrderStatus::Returned->value . '") THEN (SELECT COALESCE(SUM(order_items.quantity * product_variants.cost_price), 0) FROM order_items JOIN product_variants ON order_items.product_variant_id = product_variants.id WHERE order_items.order_id = orders.id) ELSE 0 END) as profit')
            )->where('created_at', '>=', now()->subDays($chartDays)->startOfDay())
            ->whereNotIn('status', [OrderStatus::Cancelled->value, OrderStatus::Returned->value]);
            if ($branchId) $dailyQuery->where('branch_id', $branchId);
            $dailySales = (clone $dailyQuery)->groupBy('date')->orderBy('date')->get();

            $chartData = [];
            for ($i = $chartDays; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $chartData[$date] = ['label' => $date, 'revenue' => 0, 'count' => 0, 'profit' => 0];
            }
            foreach ($dailySales as $sale) {
                if (isset($chartData[$sale->date])) {
                    $chartData[$sale->date] = [
                        'label' => $sale->date,
                        'revenue' => (float) $sale->revenue,
                        'count' => (int) $sale->count,
                        'profit' => (float) ($sale->profit ?? 0),
                    ];
                }
            }
            $chartLabels = collect($chartData)->map(fn($d, $k) => \Carbon\Carbon::parse($k)->format('d M'))->values();
        } else {
            $driver = DB::connection()->getDriverName();
            $monthFormat = match($driver) {
                'sqlite' => "strftime('%Y-%m', created_at) as month",
                'pgsql' => "TO_CHAR(created_at, 'YYYY-MM') as month",
                default => "DATE_FORMAT(created_at, '%Y-%m') as month",
            };
            $monthlyQuery = Order::select(
                DB::raw($monthFormat),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as count')
            )->where('created_at', '>=', now()->subMonths(max($chartMonths, 1))->startOfMonth())
            ->whereNotIn('status', [OrderStatus::Cancelled->value, OrderStatus::Returned->value]);
            if ($branchId) $monthlyQuery->where('branch_id', $branchId);
            $monthlySales = (clone $monthlyQuery)->groupBy('month')->orderBy('month')->get();

            $chartData = [];
            for ($i = max($chartMonths, 1); $i >= 0; $i--) {
                $month = now()->subMonths($i)->format('Y-m');
                $chartData[$month] = ['label' => $month, 'revenue' => 0, 'count' => 0, 'profit' => 0];
            }
            foreach ($monthlySales as $sale) {
                if (isset($chartData[$sale->month])) {
                    $chartData[$sale->month]['revenue'] = (float) $sale->revenue;
                    $chartData[$sale->month]['count'] = (int) $sale->count;
                }
            }
            $chartLabels = collect($chartData)->map(fn($d, $k) => \Carbon\Carbon::parse($k.'-01')->format('M Y'))->values();
        }

        // --- COGS ---
        $cogsQuery = DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotIn('orders.status', [OrderStatus::Cancelled->value, OrderStatus::Returned->value])
            ->whereNotNull('product_variants.cost_price');
        if ($branchId) $cogsQuery->where('orders.branch_id', $branchId);
        if ($dates) $cogsQuery->whereBetween('orders.created_at', [$dates['from'], $dates['to']]);
        $totalCosts = (float) (clone $cogsQuery)->select(DB::raw('SUM(order_items.quantity * product_variants.cost_price) as total_cost'))->value('total_cost') ?? 0;

        // --- Expenses ---
        $expensesQuery = Expense::query();
        if ($branchId) $expensesQuery->where('branch_id', $branchId);
        if ($dates) $expensesQuery->whereBetween('expense_date', [$dates['from'], $dates['to']]);
        $totalManualExpenses = (float) (clone $expensesQuery)->sum('amount');
        $totalExpenses = $totalManualExpenses + $totalShippingCollected;

        $netProfit = $totalProductRevenue - $totalCosts - $totalExpenses;
        $profitMargin = $totalProductRevenue > 0 ? round(($netProfit / $totalProductRevenue) * 100, 1) : 0;
        $aov = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;

        $lowStockQuery = DB::table('branch_product_variant')
            ->join('branches', 'branch_product_variant.branch_id', '=', 'branches.id')
            ->join('product_variants', 'branch_product_variant.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select(
                'products.name as product_name',
                'product_variants.color',
                'product_variants.size',
                'product_variants.sku',
                'branches.name as branch_name',
                'branch_product_variant.stock'
            )->where('branch_product_variant.stock', '>', 0)
            ->where('branch_product_variant.stock', '<', $threshold);
        if ($branchId) $lowStockQuery->where('branch_product_variant.branch_id', $branchId);
        $lowStockItems = (clone $lowStockQuery)->orderBy('branch_product_variant.stock', 'asc')->limit(10)->get();

        $recentExpenses = null;
        if (!$dates || now()->diffInDays(\Carbon\Carbon::parse($dates['from'])) <= 365) {
            $recentExpensesQuery = Expense::orderBy('expense_date', 'desc')->orderBy('created_at', 'desc');
            if ($branchId) $recentExpensesQuery->where('branch_id', $branchId);
            $recentExpenses = (clone $recentExpensesQuery)->limit(5)->get();
        }

        return compact(
            'period',
            'totalOrders', 'onlineOrders', 'offlineOrders',
            'totalRevenue', 'totalProductRevenue', 'totalShippingCollected',
            'totalCustomers', 'lowStockCount',
            'chartData', 'chartLabels',
            'lowStockItems',
            'returnedAmount', 'returnedCount',
            'returnRequestCount', 'returnRequestPending',
            'exchangeCount', 'exchangePending',
            'totalCosts', 'totalManualExpenses', 'totalExpenses',
            'netProfit', 'profitMargin',
            'aov', 'recentExpenses'
        );
    }

    private function parsePeriod(string $period, Request $request): ?array
    {
        return match ($period) {
            'today' => ['from' => now()->startOfDay(), 'to' => now()->endOfDay()],
            '7days' => ['from' => now()->subDays(6)->startOfDay(), 'to' => now()->endOfDay()],
            'month' => ['from' => now()->startOfMonth(), 'to' => now()->endOfMonth()],
            'quarter' => ['from' => now()->subMonths(2)->startOfMonth(), 'to' => now()->endOfMonth()],
            'year' => ['from' => now()->startOfYear(), 'to' => now()->endOfYear()],
            'all' => null,
            default => $request->filled(['from', 'to'])
                ? ['from' => $request->date('from')->startOfDay(), 'to' => $request->date('to')->endOfDay()]
                : ['from' => now()->startOfMonth(), 'to' => now()->endOfMonth()],
        };
    }
}
