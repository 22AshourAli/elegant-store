<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Advanced 1: Return Analytics — product/color/size return reasons.
     */
    public function returnAnalytics(Request $request)
    {
        $request->validate([
            'days' => 'integer|min:1|max:365',
            'product_id' => 'integer|exists:products,id',
        ]);

        return response()->json(
            $this->analyticsService->returnAnalytics(
                $request->integer('days', 90),
                $request->integer('product_id')
            )
        );
    }

    /**
     * Advanced 2: AOV & CLV Metrics.
     */
    public function aovAndClv(Request $request)
    {
        return response()->json(
            $this->analyticsService->aovAndClv(
                $request->integer('user_id')
            )
        );
    }

    /**
     * Advanced 3: Dead Stock & Slow-Moving Items.
     */
    public function deadStock(Request $request)
    {
        $request->validate([
            'days' => 'integer|min:7|max:365',
            'branch_id' => 'integer|exists:branches,id',
        ]);

        return response()->json(
            $this->analyticsService->deadStockReport(
                $request->integer('days', 30),
                $request->integer('branch_id')
            )
        );
    }

    /**
     * Advanced 4: Abandoned Cart Funnel Analytics.
     */
    public function cartFunnel()
    {
        return response()->json(
            $this->analyticsService->abandonedCartFunnel()
        );
    }
}
