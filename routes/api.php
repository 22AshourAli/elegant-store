<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\LoyaltyController;
use App\Http\Controllers\Api\PaymentReconciliationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShippingController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API Routes (no auth middleware yet)
|--------------------------------------------------------------------------
*/

// === Products ===
Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('api.products.show');

// === Shipping ===
Route::get('/shipping/locations', [ShippingController::class, 'locations'])->name('api.shipping.locations');
Route::match(['get', 'post'], '/shipping/calculate', [ShippingController::class, 'calculate'])->name('api.shipping.calculate');

// === Abandoned Cart Recovery ===
Route::get('/cart/recover/{token}', [CartController::class, 'recover'])->name('api.cart.recover');

// === Loyalty ===
Route::get('/loyalty/balance/{user}', [LoyaltyController::class, 'balance'])->name('api.loyalty.balance');
Route::post('/loyalty/redeem', [LoyaltyController::class, 'redeem'])->name('api.loyalty.redeem');
Route::get('/loyalty/history/{user}', [LoyaltyController::class, 'history'])->name('api.loyalty.history');

/*
|--------------------------------------------------------------------------
| Advanced Analytics & Reports (admin-level)
|--------------------------------------------------------------------------
*/

Route::prefix('analytics')->group(function () {
    // Advanced 1: Return Analytics
    Route::get('/returns', [AnalyticsController::class, 'returnAnalytics'])->name('api.analytics.returns');

    // Advanced 2: AOV & CLV
    Route::get('/aov-clv', [AnalyticsController::class, 'aovAndClv'])->name('api.analytics.aov-clv');

    // Advanced 3: Dead Stock Report
    Route::get('/dead-stock', [AnalyticsController::class, 'deadStock'])->name('api.analytics.dead-stock');

    // Advanced 4: Abandoned Cart Funnel
    Route::get('/cart-funnel', [AnalyticsController::class, 'cartFunnel'])->name('api.analytics.cart-funnel');
});

// === Payment Reconciliation (Advanced 5) ===
Route::prefix('payments')->group(function () {
    Route::post('/reconcile/{payment}', [PaymentReconciliationController::class, 'reconcilePayment']);
    Route::post('/reconcile/batch', [PaymentReconciliationController::class, 'batchReconcile']);
    Route::get('/financial-summary', [PaymentReconciliationController::class, 'financialSummary']);
    Route::post('/settlements', [PaymentReconciliationController::class, 'storeSettlement']);
});

// =============================================================================
// TEMPORARY: Database seeding endpoint — REMOVE THIS ROUTE AFTER SEEDING
// Usage: POST /api/seed-database
//        Header: X-Seed-Token: <value of SEED_TOKEN env var>
// Runs: AdminUserSeeder, BranchSeeder, CategorySeeder, ProductSeeder
// =============================================================================
Route::post('/seed-database', function () {
    $token = env('SEED_TOKEN', 'temporary-seed-token');

    if (request()->header('X-Seed-Token') !== $token) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    try {
        Artisan::call('db:seed', ['--force' => true]);
        $output = Artisan::output();

        return response()->json([
            'message' => 'Database seeded successfully',
            'output'  => $output,
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
