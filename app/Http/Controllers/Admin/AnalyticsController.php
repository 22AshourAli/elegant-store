<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\PaymentReconciliationService;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analytics,
        private readonly PaymentReconciliationService $paymentService
    ) {}

    public function index()
    {
        return view('admin.reports.index');
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