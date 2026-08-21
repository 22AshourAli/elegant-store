<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\PaymentReconciliationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShippingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Public routes: Products, Shipping, Cart Recovery — open to all clients.
| Protected routes: Analytics, Payments — require authentication.
| Analytics/Payments routes additionally require admin/manager role.
|
*/

// ============================================================
// PUBLIC ROUTES — No authentication required
// ============================================================

// Product catalog: cursor-paginated listing + detail view
Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('api.products.show');

// Shipping: governorate/city lookup and cost calculation for checkout
Route::get('/shipping/locations', [ShippingController::class, 'locations'])->name('api.shipping.locations');
Route::get('/shipping/cities', [ShippingController::class, 'cities'])->name('api.shipping.cities');
Route::post('/shipping/calculate', [ShippingController::class, 'calculate'])->name('api.shipping.calculate');

// Abandoned cart recovery: customer clicks link from email to restore cart
Route::get('/cart/recover/{token}', [CartController::class, 'recover'])->name('api.cart.recover');

// ============================================================
// PROTECTED ROUTES — Authentication required
// ============================================================

Route::middleware('auth:sanctum')->group(function () {

    // ============================================================
    // ADMIN/MANAGER ROUTES — Role-based access required
    // ============================================================

    Route::middleware('admin')->group(function () {

        // Advanced analytics: return rates, customer value, dead stock, cart funnel
        Route::prefix('analytics')->group(function () {
            Route::get('/returns', [AnalyticsController::class, 'returnAnalytics'])->name('api.analytics.returns');
            Route::get('/aov-clv', [AnalyticsController::class, 'aovAndClv'])->name('api.analytics.aov-clv');
            Route::get('/dead-stock', [AnalyticsController::class, 'deadStock'])->name('api.analytics.dead-stock');
            Route::get('/cart-funnel', [AnalyticsController::class, 'cartFunnel'])->name('api.analytics.cart-funnel');
        });

        // Payment reconciliation: match gateway transactions with internal records
        Route::prefix('payments')->group(function () {
            Route::post('/reconcile/{payment}', [PaymentReconciliationController::class, 'reconcilePayment']);
            Route::post('/reconcile/batch', [PaymentReconciliationController::class, 'batchReconcile']);
            Route::get('/financial-summary', [PaymentReconciliationController::class, 'financialSummary']);
            Route::post('/settlements', [PaymentReconciliationController::class, 'storeSettlement']);
        });
    });
});
