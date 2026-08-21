<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\CustomerWallet;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\ReturnRequestItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * ---------------------------------------------------------------
     * ADVANCED 1: Return Analytics — product/color/size analysis
     * ---------------------------------------------------------------
     */
    public function returnAnalytics(int $daysBack = 90, ?int $productId = null): array
    {
        $query = ReturnRequestItem::query()
            ->select([
                'order_items.product_variant_id',
                'order_items.product_name',
                'order_items.color',
                'order_items.size',
                DB::raw('COUNT(*) as return_count'),
                DB::raw('SUM(return_request_items.quantity) as total_returned_qty'),
                DB::raw('JSON_ARRAYAGG(return_request_items.reason_category) as reasons'),
            ])
            ->join('order_items', 'return_request_items.order_item_id', '=', 'order_items.id')
            ->join('return_requests', 'return_request_items.return_request_id', '=', 'return_requests.id')
            ->where('return_requests.created_at', '>=', Carbon::now()->subDays($daysBack));

        if ($productId) {
            $query->whereIn('order_items.product_variant_id', function ($q) use ($productId) {
                $q->select('id')->from('product_variants')->where('product_id', $productId);
            });
        }

        $results = $query
            ->groupBy('order_items.product_variant_id', 'order_items.product_name', 'order_items.color', 'order_items.size')
            ->orderByDesc('return_count')
            ->limit(50)
            ->get();

        // Aggregate by reason categories
        $reasonDistribution = ReturnRequest::where('return_requests.created_at', '>=', Carbon::now()->subDays($daysBack))
            ->join('return_request_items', 'return_requests.id', '=', 'return_request_items.return_request_id')
            ->select(DB::raw('COALESCE(NULLIF(return_request_items.reason_detail, ""), return_requests.reason, return_request_items.reason_category) as reason'), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw('COALESCE(NULLIF(return_request_items.reason_detail, ""), return_requests.reason, return_request_items.reason_category)'))
            ->orderByDesc('total')
            ->get();

        // Most returned sizes
        $topSizes = ReturnRequestItem::query()
            ->join('order_items', 'return_request_items.order_item_id', '=', 'order_items.id')
            ->select('order_items.size', DB::raw('COUNT(*) as total'))
            ->groupBy('order_items.size')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Most returned colors
        $topColors = ReturnRequestItem::query()
            ->join('order_items', 'return_request_items.order_item_id', '=', 'order_items.id')
            ->select('order_items.color', DB::raw('COUNT(*) as total'))
            ->groupBy('order_items.color')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'by_variant' => $results,
            'by_reason' => $reasonDistribution,
            'by_size' => $topSizes,
            'by_color' => $topColors,
            'total_return_requests' => ReturnRequest::where('created_at', '>=', Carbon::now()->subDays($daysBack))->count(),
        ];
    }

    /**
     * ---------------------------------------------------------------
     * ADVANCED 2: AOV & CLV Metrics
     * ---------------------------------------------------------------
     */
    public function aovAndClv(int $userId = null): array
    {
        $query = Order::where('payment_status', PaymentStatus::Paid->value);

        // AOV: Average Order Value (paid orders only)
        $aov = (float) (clone $query)->avg('total') ?? 0;
        $totalOrders = (clone $query)->count();

        // CLV: Customer Lifetime Value — average per customer
        $clvData = Order::where('payment_status', PaymentStatus::Paid->value)
            ->whereNotNull('user_id')
            ->select('user_id', DB::raw('SUM(total) as lifetime_value'), DB::raw('COUNT(*) as order_count'))
            ->groupBy('user_id')
            ->get();

        $avgClv = (float) $clvData->avg('lifetime_value') ?? 0;
        $maxClv = (float) $clvData->max('lifetime_value') ?? 0;

        // Top 10 VIP customers
        $vipCustomers = $clvData->sortByDesc('lifetime_value')->take(10)->values();

        $result = [
            'aov' => $aov,
            'total_paid_orders' => $totalOrders,
            'avg_clv' => $avgClv,
            'max_clv' => $maxClv,
            'vip_customers' => $vipCustomers,
        ];

        // If a specific userId is requested, return their personal metrics
        if ($userId) {
            $customerData = CustomerWallet::where('user_id', $userId)->first();
            $orders = (clone $query)->where('user_id', $userId);

            $result['customer'] = [
                'user_id' => $userId,
                'lifetime_spent' => (float) ($customerData?->lifetime_spent ?? 0),
                'order_count' => (clone $orders)->count(),
                'average_order_value' => (float) (clone $orders)->avg('total') ?? 0,
                'loyalty_points' => (int) ($customerData?->loyalty_points ?? 0),
            ];
        }

        return $result;
    }

    /**
     * ---------------------------------------------------------------
     * ADVANCED 3: Dead Stock & Slow-Moving Items
     * ---------------------------------------------------------------
     */
    public function deadStockReport(int $daysWithoutSale = 30, ?int $branchId = null): array
    {
        $cutoff = Carbon::now()->subDays($daysWithoutSale);

        // Find variants that have stock but haven't been sold in X days
        $deadStock = ProductVariant::query()
            ->select([
                'product_variants.id',
                'product_variants.product_id',
                'product_variants.sku',
                DB::raw("COALESCE(price_override, products.base_price) as variant_price"),
                DB::raw('COALESCE(SUM(bpv.stock), 0) as total_stock'),
            ])
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('branch_product_variant as bpv', 'product_variants.id', '=', 'bpv.product_variant_id')
            ->where('bpv.stock', '>', 0)
            ->whereNotIn('product_variants.id', function ($q) use ($cutoff) {
                $q->select('product_variant_id')
                    ->from('order_items')
                    ->where('created_at', '>=', $cutoff);
            });

        if ($branchId) {
            $deadStock->where('bpv.branch_id', $branchId);
        }

        $items = $deadStock
            ->groupBy('product_variants.id', 'product_variants.product_id', 'product_variants.sku', 'variant_price')
            ->having('total_stock', '>', 0)
            ->orderBy('total_stock', 'desc')
            ->limit(100)
            ->get();

        $totalDeadStockValue = $items->sum(fn($i) => $i->total_stock * (float) $i->variant_price);

        // Also get slow-movers (sold 1-2 units in the period)
        $slowMoving = ProductVariant::query()
            ->select([
                'product_variants.id',
                'product_variants.sku',
                DB::raw('COALESCE(SUM(bpv.stock), 0) as total_stock'),
                DB::raw('COALESCE(SUM(oi.quantity), 0) as sold_qty'),
            ])
            ->join('branch_product_variant as bpv', 'product_variants.id', '=', 'bpv.product_variant_id')
            ->leftJoin('order_items as oi', function ($j) use ($cutoff) {
                $j->on('product_variants.id', '=', 'oi.product_variant_id')
                    ->where('oi.created_at', '>=', $cutoff);
            })
            ->groupBy('product_variants.id', 'product_variants.sku')
            ->having('total_stock', '>', 0)
            ->having('sold_qty', '>', 0)
            ->having('sold_qty', '<=', 2)
            ->limit(50)
            ->get();

        return [
            'days_without_sale' => $daysWithoutSale,
            'dead_stock_count' => $items->count(),
            'dead_stock_total_value' => $totalDeadStockValue,
            'dead_stock_items' => $items,
            'slow_moving_items' => $slowMoving,
        ];
    }

    /**
     * ---------------------------------------------------------------
     * ADVANCED 4: Abandoned Cart Funnel Analytics
     * ---------------------------------------------------------------
     */
    public function abandonedCartFunnel(): array
    {
        $totalCarts = DB::table('abandoned_carts')->count();

        $byStep = DB::table('abandoned_carts')
            ->select('checkout_step', DB::raw('COUNT(*) as count'))
            ->whereNotNull('checkout_step')
            ->groupBy('checkout_step')
            ->orderByDesc('count')
            ->get();

        $byStepWithPercent = $byStep->map(fn($row) => [
            'step' => $row->checkout_step,
            'count' => $row->count,
            'percent_of_total' => $totalCarts > 0
                ? round(($row->count / $totalCarts) * 100, 2)
                : 0,
        ]);

        // Abandonment trend (last 7 days)
        $dailyTrend = DB::table('abandoned_carts')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as carts_created'),
                DB::raw('SUM(CASE WHEN status = "recovered" THEN 1 ELSE 0 END) as recovered'),
                DB::raw('SUM(CASE WHEN status = "converted" THEN 1 ELSE 0 END) as converted')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return [
            'total_abandoned_carts' => $totalCarts,
            'by_checkout_step' => $byStepWithPercent,
            'daily_trend_7days' => $dailyTrend,
            'recovery_rate_percent' => $totalCarts > 0
                ? round((DB::table('abandoned_carts')
                    ->whereIn('status', ['recovered', 'converted'])->count() / $totalCarts) * 100, 2)
                : 0,
        ];
    }
}
