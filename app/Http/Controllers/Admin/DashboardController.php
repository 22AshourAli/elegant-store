<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Exchange;
use App\Models\Expense;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $branchId = $user->isManager() ? $user->branch_id : null;

        $orderQuery = Order::query();
        $revenueQuery = Order::whereNotIn('status', ['cancelled', 'returned']);
        $returnedQuery = Order::where('status', 'returned');
        $lowStockQuery = ProductVariant::whereHas('branches', function ($q) use ($branchId) {
            $q->where('stock', '<', 2);
            if ($branchId) $q->where('branch_id', $branchId);
        });

        if ($branchId) {
            $orderQuery->where('branch_id', $branchId);
            $revenueQuery->where('branch_id', $branchId);
            $returnedQuery->where('branch_id', $branchId);
        }

        $totalOrders = (clone $orderQuery)->count();
        $totalRevenue = (float) (clone $revenueQuery)->sum('total');
        $totalProductRevenue = (float) (clone $revenueQuery)->sum('subtotal');
        $totalShippingCollected = (float) (clone $revenueQuery)->sum('shipping_cost');
        $totalCustomers = User::where('role', 'customer')->count();
        $lowStockCount = (clone $lowStockQuery)->count();
        $returnedAmount = (clone $returnedQuery)->sum('total');
        $returnedCount = (clone $returnedQuery)->count();

        // Return & Exchange Requests
        $returnRequestQuery = ReturnRequest::query();
        $exchangeQuery = Exchange::query();
        if ($branchId) {
            $returnRequestQuery->whereHas('order', fn($q) => $q->where('branch_id', $branchId));
            $exchangeQuery->whereHas('order', fn($q) => $q->where('branch_id', $branchId));
        }
        $returnRequestCount = (clone $returnRequestQuery)->count();
        $returnRequestPending = (clone $returnRequestQuery)->where('status', 'pending')->count();
        $exchangeCount = (clone $exchangeQuery)->count();
        $exchangePending = (clone $exchangeQuery)->where('status', 'pending')->count();

        // 1. Weekly Sales Analytics
        $weeklyQuery = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total) as revenue'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subDays(6)->startOfDay())
        ->whereNotIn('status', ['cancelled', 'returned']);

        if ($branchId) $weeklyQuery->where('branch_id', $branchId);
        $weeklySales = (clone $weeklyQuery)->groupBy('date')->orderBy('date')->get();

        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $weeklyData[$date] = [
                'date' => $date,
                'revenue' => 0,
                'count' => 0
            ];
        }
        foreach ($weeklySales as $sale) {
            if (isset($weeklyData[$sale->date])) {
                $weeklyData[$sale->date]['revenue'] = (float)$sale->revenue;
                $weeklyData[$sale->date]['count'] = (int)$sale->count;
            }
        }

        $driver = DB::connection()->getDriverName();
        $monthFormat = match($driver) {
            'sqlite' => "strftime('%Y-%m', created_at) as month",
            'pgsql' => "TO_CHAR(created_at, 'YYYY-MM') as month",
            default => "DATE_FORMAT(created_at, '%Y-%m') as month",
        };

        // 2. Annual Sales Analytics
        $monthlyQuery = Order::select(
            DB::raw($monthFormat),
            DB::raw('SUM(total) as revenue')
        )
        ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
        ->whereNotIn('status', ['cancelled', 'returned']);

        if ($branchId) $monthlyQuery->where('branch_id', $branchId);
        $monthlySales = (clone $monthlyQuery)->groupBy('month')->orderBy('month')->get();

        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthlyData[$month] = [
                'month' => $month,
                'revenue' => 0
            ];
        }
        foreach ($monthlySales as $sale) {
            if (isset($monthlyData[$sale->month])) {
                $monthlyData[$sale->month]['revenue'] = (float)$sale->revenue;
            }
        }

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
            )
            ->where('branch_product_variant.stock', '<', 2);

        if ($branchId) $lowStockQuery->where('branch_product_variant.branch_id', $branchId);
        $lowStockItems = (clone $lowStockQuery)->orderBy('branch_product_variant.stock', 'asc')->limit(10)->get();

        // 3. Cost of Goods Sold (COGS)
        $cogsQuery = DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotIn('orders.status', ['cancelled', 'returned'])
            ->whereNotNull('product_variants.cost_price');

        if ($branchId) $cogsQuery->where('orders.branch_id', $branchId);

        $totalCosts = (float) (clone $cogsQuery)->select(DB::raw('SUM(order_items.quantity * product_variants.cost_price) as total_cost'))->value('total_cost') ?? 0;

        // 4. Total Expenses (all time, matching revenue period)
        $expensesQuery = Expense::query();
        if ($branchId) $expensesQuery->where('branch_id', $branchId);
        $totalManualExpenses = (float) (clone $expensesQuery)->sum('amount');
        $totalExpenses = $totalManualExpenses + $totalShippingCollected;

        // 5. Monthly totals for aligned comparison
        $monthlyRevenueQuery = Order::whereNotIn('status', ['cancelled', 'returned'])
            ->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month);
        if ($branchId) $monthlyRevenueQuery->where('branch_id', $branchId);
        $monthlyRevenue = (float) (clone $monthlyRevenueQuery)->sum('total');
        $monthlyProductRevenue = (float) (clone $monthlyRevenueQuery)->sum('subtotal');
        $monthlyShippingCollected = (float) (clone $monthlyRevenueQuery)->sum('shipping_cost');

        $monthlyCostsQuery = DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotIn('orders.status', ['cancelled', 'returned'])
            ->whereNotNull('product_variants.cost_price')
            ->whereYear('orders.created_at', now()->year)->whereMonth('orders.created_at', now()->month);
        if ($branchId) $monthlyCostsQuery->where('orders.branch_id', $branchId);
        $monthlyCosts = (float) (clone $monthlyCostsQuery)->select(DB::raw('SUM(order_items.quantity * product_variants.cost_price) as total_cost'))->value('total_cost') ?? 0;

        $monthlyExpensesQuery = Expense::whereYear('expense_date', now()->year)->whereMonth('expense_date', now()->month);
        if ($branchId) $monthlyExpensesQuery->where('branch_id', $branchId);
        $monthlyManualExpenses = (float) (clone $monthlyExpensesQuery)->sum('amount');
        $monthlyExpenses = $monthlyManualExpenses + $monthlyShippingCollected;

        // Monthly + All-time Net Profit
        $netProfit = $totalProductRevenue - $totalCosts - $totalExpenses;
        $monthlyNetProfit = $monthlyProductRevenue - $monthlyCosts - $monthlyExpenses;

        // 6. Recent Expenses (last 5)
        $recentExpensesQuery = Expense::orderBy('expense_date', 'desc')->orderBy('created_at', 'desc');
        if ($branchId) $recentExpensesQuery->where('branch_id', $branchId);
        $recentExpenses = (clone $recentExpensesQuery)->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalOrders',
            'totalRevenue',
            'totalProductRevenue',
            'totalShippingCollected',
            'totalCustomers',
            'lowStockCount',
            'weeklyData',
            'monthlyData',
            'lowStockItems',
            'returnedAmount',
            'returnedCount',
            'returnRequestCount',
            'returnRequestPending',
            'exchangeCount',
            'exchangePending',
            'totalCosts',
            'totalManualExpenses',
            'totalExpenses',
            'netProfit',
            'monthlyRevenue',
            'monthlyProductRevenue',
            'monthlyShippingCollected',
            'monthlyCosts',
            'monthlyManualExpenses',
            'monthlyExpenses',
            'monthlyNetProfit',
            'recentExpenses'
        ));
    }
}
