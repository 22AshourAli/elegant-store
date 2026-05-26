<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\User;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOrders = Order::count();
        $totalRevenue = (float) Order::where('status', '!=', 'cancelled')->sum('total');
        $totalCustomers = User::where('role', 'customer')->count();
        $lowStockCount = ProductVariant::whereHas('branches', function ($q) {
            $q->where('stock', '<', 5);
        })->count();

        // 1. Weekly Sales Analytics
        $weeklySales = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total) as revenue'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subDays(6)->startOfDay())
        ->where('status', '!=', 'cancelled')
        ->groupBy('date')
        ->orderBy('date')
        ->get();

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

        $monthFormat = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at) as month"
            : "DATE_FORMAT(created_at, '%Y-%m') as month";

        // 2. Annual Sales Analytics
        $monthlySales = Order::select(
            DB::raw($monthFormat),
            DB::raw('SUM(total) as revenue')
        )
        ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
        ->where('status', '!=', 'cancelled')
        ->groupBy('month')
        ->orderBy('month')
        ->get();

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

        $lowStockItems = DB::table('branch_product_variant')
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
            ->where('branch_product_variant.stock', '<', 5)
            ->orderBy('branch_product_variant.stock', 'asc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalOrders',
            'totalRevenue',
            'totalCustomers',
            'lowStockCount',
            'weeklyData',
            'monthlyData',
            'lowStockItems'
        ));
    }
}
