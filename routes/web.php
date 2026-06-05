<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\CategoryController as ShopCategoryController;
use App\Http\Controllers\Shop\ProductController as ShopProductController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\WishlistController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\PaymobWebhookController;
use App\Http\Controllers\Shop\OrderController as ShopOrderController;
use App\Http\Controllers\Shop\NotificationController;
use App\Http\Controllers\ProfileController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/sitemap.xml', function () {
    $xml = Cache::remember('sitemap_xml', 86400, function () {
        $categories = App\Models\Category::whereRaw('"is_active" = true')->get(['id', 'slug']);
        $products   = App\Models\Product::whereRaw('"is_active" = true')->get(['id', 'slug']);
        return view('sitemap', compact('categories', 'products'))->render();
    });
    return response($xml, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/dashboard', function () {
    if (auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isManager())) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('home');
})->name('dashboard')->middleware('auth');

Route::view('/return-policy', 'shop.return-policy')->name('return.policy');

Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['ar', 'en'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');

Route::get('/category/{slug}', [ShopCategoryController::class, 'show'])->name('shop.category');
Route::get('/product/{slug}', [ShopProductController::class, 'show'])->name('shop.product');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{variant}', [CartController::class, 'add'])->name('cart.add');
Route::post('/buy-now/{variant}', [CartController::class, 'buyNow'])->name('cart.buy-now');
Route::patch('/cart/{variant}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{variant}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/count', function(\App\Services\CartService $cart) {
    return response()->json(['count' => $cart->count()]);
})->name('cart.count');
Route::post('/coupon/apply', [CartController::class, 'applyCoupon'])->name('coupon.apply');
Route::post('/coupon/remove', [CartController::class, 'removeCoupon'])->name('coupon.remove');

Route::get('/wishlist', [WishlistController::class, 'index'])->middleware('auth')->name('wishlist.index');
Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->middleware('auth')->name('wishlist.toggle');
Route::get('/wishlist/count', function() {
    return response()->json(['count' => auth()->user()->wishlist()->count()]);
})->middleware('auth')->name('wishlist.count');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::get('/checkout/auth', [CheckoutController::class, 'showAuthForm'])->name('checkout.auth');
Route::post('/checkout/identify', [CheckoutController::class, 'identifyCustomer'])->name('checkout.identify');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/payment/mock/{order}', [CheckoutController::class, 'showMockPaymentForm'])->name('payment.mock');
    Route::post('/payment/mock/{order}/process', [CheckoutController::class, 'processMockPayment'])->name('payment.mock.process');
});

Route::post('/webhooks/paymob', [PaymobWebhookController::class, 'handle'])->name('paymob.webhook');

Route::middleware('auth')->group(function () {
    Route::get('/orders', [ShopOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [ShopOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [ShopOrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/returns', [\App\Http\Controllers\Shop\ReturnRequestController::class, 'index'])->name('returns.index');
    Route::post('/orders/{order}/return', [\App\Http\Controllers\Shop\ReturnRequestController::class, 'store'])->name('returns.store');

    Route::get('/exchanges', [\App\Http\Controllers\Shop\ExchangeRequestController::class, 'index'])->name('exchanges.index');
    Route::get('/orders/{order}/exchange', [\App\Http\Controllers\Shop\ExchangeRequestController::class, 'create'])->name('exchanges.create');
    Route::post('/orders/{order}/exchange', [\App\Http\Controllers\Shop\ExchangeRequestController::class, 'store'])->name('exchanges.store');
});

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('branches', BranchController::class);
    Route::resource('users', UserController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('coupons', CouponController::class);
    Route::resource('users', UserController::class);
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show']);
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('/orders/{order}/invoice', [App\Http\Controllers\Admin\OrderController::class, 'invoice'])->name('orders.invoice');

    // Return Requests
    Route::get('/returns', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'index'])->name('returns.index');
    Route::get('/returns/{return}', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'show'])->name('returns.show');
    Route::post('/returns/{return}/approve', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'approve'])->name('returns.approve');
    Route::post('/returns/{return}/reject', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'reject'])->name('returns.reject');

    // Exchange Requests (standalone)
    Route::get('/exchanges', [\App\Http\Controllers\Admin\ExchangeController::class, 'index'])->name('exchanges.index');
    Route::get('/exchanges/{exchange}', [\App\Http\Controllers\Admin\ExchangeController::class, 'show'])->name('exchanges.show');
    Route::any('/exchanges/{exchange}/approve', [\App\Http\Controllers\Admin\ExchangeController::class, 'approve'])->name('exchanges.approve');
    Route::any('/exchanges/{exchange}/reject', [\App\Http\Controllers\Admin\ExchangeController::class, 'reject'])->name('exchanges.reject');

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\Admin\NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\Admin\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/unread-count', [App\Http\Controllers\Admin\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');

    // Expenses
    Route::resource('expenses', \App\Http\Controllers\Admin\ExpenseController::class);

    // WhatsApp Marketing
    Route::get('/whatsapp', [\App\Http\Controllers\Admin\WhatsappMarketingController::class, 'index'])->name('whatsapp.index');
    Route::get('/whatsapp/bulk', [\App\Http\Controllers\Admin\WhatsappMarketingController::class, 'bulkForm'])->name('whatsapp.bulk');
    Route::post('/whatsapp/bulk/send', [\App\Http\Controllers\Admin\WhatsappMarketingController::class, 'sendBulk'])->name('whatsapp.bulk.send');
    Route::get('/whatsapp/next-in-line', [\App\Http\Controllers\Admin\WhatsappMarketingController::class, 'nextInLine'])->name('whatsapp.next');
    Route::get('/whatsapp/{user}', [\App\Http\Controllers\Admin\WhatsappMarketingController::class, 'show'])->name('whatsapp.show');
    Route::post('/whatsapp/{user}/send', [\App\Http\Controllers\Admin\WhatsappMarketingController::class, 'sendMessage'])->name('whatsapp.send');
    Route::post('/whatsapp/{user}/mark-sent', [\App\Http\Controllers\Admin\WhatsappMarketingController::class, 'markSent'])->name('whatsapp.mark-sent');
});

require __DIR__.'/auth.php';
