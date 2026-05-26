<?php

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
    $categories = App\Models\Category::where('is_active', true)->get();
    $products = App\Models\Product::where('is_active', true)->get();
    return response()->view('sitemap', compact('categories', 'products'))->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/dashboard', function () {
    if (auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isManager())) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('home');
})->name('dashboard')->middleware('auth');

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

Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->middleware('auth')->name('wishlist.toggle');

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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
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
use App\Http\Controllers\Admin\OrderController as AdminOrderController;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('branches', BranchController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('coupons', CouponController::class);
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show']);
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('/orders/{order}/invoice', [App\Http\Controllers\Admin\OrderController::class, 'invoice'])->name('orders.invoice');

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\Admin\NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\Admin\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/unread-count', [App\Http\Controllers\Admin\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
});

require __DIR__.'/auth.php';
