<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\LoyaltyController;
use App\Http\Controllers\Api\PaymentReconciliationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShippingController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
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
Route::post('/shipping/calculate', [ShippingController::class, 'calculate'])->name('api.shipping.calculate');

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

// =============================================================================
// TEMPORARY: Mail connectivity test endpoint — REMOVE AFTER DEBUGGING
// Usage: POST /api/test-mail
//        Header: X-Mail-Token: <value of MAIL_TEST_TOKEN env var>
//        Body (JSON): { "to": "you@example.com" }   (optional — defaults to MAIL_FROM_ADDRESS)
// Returns: JSON with SMTP config snapshot, TCP check result, and send result
// =============================================================================
Route::post('/test-mail', function () {
    $token = env('MAIL_TEST_TOKEN');

    if (empty($token) || request()->header('X-Mail-Token') !== $token) {
        return response()->json(['error' => 'Unauthorized — set MAIL_TEST_TOKEN and pass it as X-Mail-Token header'], 401);
    }

    $mailer   = config('mail.default');
    $host     = config('mail.mailers.smtp.host');
    $port     = (int) config('mail.mailers.smtp.port', 587);
    $username = config('mail.mailers.smtp.username');
    $from     = config('mail.from.address');
    $enc      = config('mail.mailers.smtp.encryption');
    $to       = request()->input('to', $from);

    $result = [
        'config' => [
            'mailer'   => $mailer,
            'host'     => $host,
            'port'     => $port,
            'username' => $username,
            'from'     => $from,
            'encryption' => $enc,
        ],
        'tcp_check'  => null,
        'send_result' => null,
        'error'       => null,
    ];

    // Warn if mailer is not smtp
    if ($mailer !== 'smtp') {
        $result['warning'] = "MAIL_MAILER is '{$mailer}', not 'smtp'. Emails will not be delivered via Gmail SMTP.";
    }

    // TCP connectivity check
    $socket = @fsockopen($host, $port, $errno, $errstr, 10);
    if ($socket === false) {
        $result['tcp_check'] = "FAILED: [{$errno}] {$errstr}";
        Log::error('test-mail endpoint: TCP connection failed', ['host' => $host, 'port' => $port, 'error' => $errstr]);
        return response()->json($result, 502);
    }
    fclose($socket);
    $result['tcp_check'] = "OK — connected to {$host}:{$port}";

    // Attempt to send
    if (empty($to)) {
        $result['send_result'] = 'SKIPPED — no recipient address (pass "to" in request body or set MAIL_FROM_ADDRESS)';
        return response()->json($result);
    }

    try {
        Mail::raw(
            "This is a test email from Elegant Store sent at " . now()->toDateTimeString() . ".\n\n"
            . "If you received this, Gmail SMTP is configured correctly on Railway.",
            function (Message $message) use ($to, $from) {
                $message->to($to)
                        ->subject('[Elegant Store] SMTP Test — ' . now()->toDateTimeString())
                        ->from($from, config('mail.from.name'));
            }
        );

        $result['send_result'] = "OK — test email sent to {$to}";
        Log::info('test-mail endpoint: test email sent successfully', ['to' => $to]);
    } catch (\Exception $e) {
        $result['send_result'] = 'FAILED';
        $result['error']       = $e->getMessage();
        Log::error('test-mail endpoint: failed to send test email', ['to' => $to, 'error' => $e->getMessage()]);
        return response()->json($result, 500);
    }

    return response()->json($result);
});
