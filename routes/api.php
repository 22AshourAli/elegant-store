<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\PaymentReconciliationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShippingController;
use Illuminate\Support\Facades\Route;

// ============================================================
// PUBLIC ROUTES — No authentication required
// ============================================================

Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('api.products.show');

Route::get('/shipping/locations', [ShippingController::class, 'locations'])->name('api.shipping.locations');
Route::get('/shipping/cities', [ShippingController::class, 'cities'])->name('api.shipping.cities');
Route::post('/shipping/calculate', [ShippingController::class, 'calculate'])->name('api.shipping.calculate');

Route::get('/cart/recover/{token}', [CartController::class, 'recover'])->name('api.cart.recover');

// ============================================================
// PROTECTED ROUTES — Authentication + Admin role required
// ============================================================

Route::middleware('auth:sanctum', 'admin')->group(function () {

    Route::prefix('analytics')->group(function () {
        Route::get('/returns', [AnalyticsController::class, 'returnAnalytics'])->name('api.analytics.returns');
        Route::get('/aov-clv', [AnalyticsController::class, 'aovAndClv'])->name('api.analytics.aov-clv');
        Route::get('/dead-stock', [AnalyticsController::class, 'deadStock'])->name('api.analytics.dead-stock');
        Route::get('/cart-funnel', [AnalyticsController::class, 'cartFunnel'])->name('api.analytics.cart-funnel');
    });

    Route::prefix('payments')->group(function () {
        Route::post('/reconcile/{payment}', [PaymentReconciliationController::class, 'reconcilePayment']);
        Route::post('/reconcile/batch', [PaymentReconciliationController::class, 'batchReconcile']);
        Route::get('/financial-summary', [PaymentReconciliationController::class, 'financialSummary']);
        Route::post('/settlements', [PaymentReconciliationController::class, 'storeSettlement']);
    });
});
