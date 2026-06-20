<?php

namespace App\Http\Controllers\Shop;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Governorate;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    public function index(CartService $cart, ShippingService $shippingService)
    {
        if (!auth()->check()) {
            return redirect()->route('checkout.auth');
        }

        $cartItems = $cart->getEnrichedCart();
        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('error', 'السلة فارغة حالياً.');
        }

        $baseTotal = $cart->baseTotal();
        $discount = $cart->getDiscount();
        $total = $cart->total();
        $appliedCoupon = $cart->getAppliedCoupon();

        $previousOrders = auth()->user()->orders()->where('status', '!=', OrderStatus::Cancelled->value)->count();
        if ($previousOrders === 0) {
            $shipping = 0;
            $shippingKnown = true;
        } else {
            $oldGovId = old('governorate_id');
            $oldCityId = old('city_id');
            if ($oldGovId) {
                $result = $shippingService->calculateCost((int) $oldGovId, $oldCityId ? (int) $oldCityId : null, $total);
                $shipping = $result['final_cost'];
                $shippingKnown = true;
            } else {
                $shipping = 0;
                $shippingKnown = false;
            }
        }
        $finalTotal = $total + $shipping;

        $hasActiveCoupons = \App\Models\Coupon::where('is_active', true)
            ->where(function($q) { $q->whereNull('valid_from')->orWhere('valid_from', '<=', now()); })
            ->where(function($q) { $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()); })
            ->exists();

        $governorates = $shippingService->getCheckoutLocations();

        return view('shop.checkout', compact('cartItems', 'baseTotal', 'discount', 'total', 'appliedCoupon', 'shipping', 'shippingKnown', 'finalTotal', 'hasActiveCoupons', 'governorates'));
    }

    public function showAuthForm()
    {
        if (auth()->check()) {
            return redirect()->route('checkout');
        }
        return view('shop.checkout_auth');
    }

    public function identifyCustomer(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = $request->email;
        $userExists = \App\Models\User::where('email', $email)->exists();

        // Save checkout as the intended URL for redirection after login or register
        session(['url.intended' => route('checkout')]);

        if ($userExists) {
            return redirect()->route('login', ['email' => $email])
                ->with('success', 'البريد الإلكتروني مسجل بالفعل لدينا. يرجى إدخال كلمة المرور للمتابعة وإتمام الشراء.');
        }

        return redirect()->route('register', ['email' => $email])
            ->with('success', 'حساب جديد! يرجى إكمال إدخال الاسم وكلمة المرور للمتابعة والتسجيل لإتمام الشراء.');
    }

    public function store(Request $request, CartService $cart, CheckoutService $checkout)
    {
        $request->validate([
            'shipping_address' => 'required|string|max:1000',
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'required|exists:cities,id',
            'payment_method' => 'required|in:cash,card,wallet',
            'phone' => ['required', 'string', 'regex:/^(01)[0-9]{9}$/', 'size:11'],
            'notes' => 'nullable|string|max:1000',
        ]);

        $cartItems = $cart->getEnrichedCart();
        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('error', 'السلة فارغة.');
        }

        // Pre-order integrity check: verify no product/variant was soft-deleted or deactivated
        // between the time the user loaded the cart and when they submit the order.
        $variantIds = array_column($cartItems, 'variant_id');
        $validVariants = \App\Models\ProductVariant::with('product')
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        foreach ($cartItems as $item) {
            $variant = $validVariants->get($item['variant_id']);
            if (!$variant || $variant->trashed() || !$variant->product || $variant->product->deleted_at !== null) {
                return redirect()->route('cart.index')
                    ->with('error', 'بعض المنتجات في سلتك لم تعد متاحة. تم تحديث السلة تلقائياً.');
            }
        }

        try {
            $order = DB::transaction(function () use ($request, $cart, $checkout, $cartItems) {
                $data = $request->all();
                $data['branch_id'] = 1;
                $data['subtotal'] = $cart->baseTotal();
                $data['discount'] = $cart->getDiscount();

                return $checkout->createOrder(auth()->user(), $cartItems, $data);
            });

            if ($request->payment_method === 'cash') {
                session()->forget('cart');
                return redirect()->route('orders.show', $order)
                    ->with('success', __('global.order_placed_success', ['id' => $order->id]));
            }

            try {
                $response = $this->processCardPayment($order, $request->payment_method, $request->phone);
                session()->forget('cart');
                return $response;
            } catch (\Exception $e) {
                DB::transaction(function () use ($order) {
                    $order->update([
                        'status' => OrderStatus::Cancelled->value,
                        'payment_status' => PaymentStatus::Failed->value,
                    ]);
                    $order->payment()->update([
                        'status' => PaymentStatus::Failed->value,
                        'response' => ['error' => $e->getMessage()],
                    ]);
                });
                throw $e;
            }

        } catch (\Exception $e) {
            $message = app()->getLocale() === 'ar'
                ? 'عذراً، حدث خطأ أثناء معالجة طلبك. يرجى المحاولة مرة أخرى.'
                : 'Sorry, an error occurred while processing your order. Please try again.';
            return redirect()->route('checkout')
                ->withInput()
                ->with('error', $message);
        }
    }

    private function processCashPayment($order)
    {
        return redirect()->route('orders.show', $order);
    }

    private function processCardPayment($order, $paymentMethod, $phone)
    {
        return $this->initiatePaymobPayment($order, $paymentMethod, $phone);
    }

    private function initiatePaymobPayment($order, $method, $phone)
    {
        // Check if Paymob API key is configured. If not, redirect to mock payment gateway
        if (empty(config('services.paymob.api_key')) || config('services.paymob.api_key') === 'your-paymob-api-key') {
            return redirect()->route('payment.mock', ['order' => $order->id, 'method' => $method]);
        }

        try {
            // Step 1: Authentication Request
            $authResponse = Http::post('https://accept.paymob.com/api/auth/tokens', [
                'api_key' => config('services.paymob.api_key'),
            ])->json();

            if (!isset($authResponse['token'])) {
                throw new \Exception('فشل الحصول على رمز مصادقة Paymob');
            }
            $authToken = $authResponse['token'];

            // Step 2: Order Registration
            $orderResponse = Http::post('https://accept.paymob.com/api/ecommerce/orders', [
                'auth_token' => $authToken,
                'delivery_needed' => "false",
                'amount_cents' => $order->total * 100,
                'currency' => 'EGP',
                'merchant_order_id' => $order->id,
            ])->json();

            if (!isset($orderResponse['id'])) {
                throw new \Exception('فشل تسجيل الطلب في Paymob');
            }
            $paymobOrderId = $orderResponse['id'];

            // Step 3: Payment Key Request
            $integrationId = $method === 'card'
                ? config('services.paymob.integration_id_card')
                : config('services.paymob.integration_id_wallet');

            $paymentKeyResponse = Http::post('https://accept.paymob.com/api/acceptance/payment_keys', [
                'auth_token' => $authToken,
                'amount_cents' => $order->total * 100,
                'expiration' => 3600,
                'order_id' => $paymobOrderId,
                "billing_data" => [
                    "apartment" => "NA",
                    "email" => $order->user->email,
                    "floor" => "NA",
                    "first_name" => $order->user->name,
                    "street" => "NA",
                    "building" => "NA",
                    "phone_number" => $phone ?? "01000000000",
                    "shipping_method" => "NA",
                    "postal_code" => "NA",
                    "city" => "NA",
                    "country" => "EG",
                    "last_name" => "NA",
                    "state" => "NA"
                ],
                'currency' => 'EGP',
                'integration_id' => $integrationId,
                "lock_order_when_paid" => "true"
            ])->json();

            if (!isset($paymentKeyResponse['token'])) {
                throw new \Exception('فشل الحصول على مفتاح دفع Paymob');
            }
            $paymentToken = $paymentKeyResponse['token'];

            if ($method === 'card') {
                $iframeId = config('services.paymob.iframe_id', '843516');
                $iframeUrl = config('services.paymob.iframe_url') . $iframeId . '?payment_token=' . $paymentToken;
                return redirect($iframeUrl);
            } else {
                // Wallet redirection request
                $walletResponse = Http::post('https://accept.paymob.com/api/acceptance/payments/pay', [
                    'source' => [
                        'identifier' => $phone,
                        'subtype' => 'WALLET'
                    ],
                    'payment_token' => $paymentToken
                ])->json();

                if (isset($walletResponse['redirect_url'])) {
                    return redirect($walletResponse['redirect_url']);
                }
                throw new \Exception('فشل توليد رابط الدفع للمحفظة الإلكترونية.');
            }

        } catch (\Exception $e) {
            $order->update(['status' => OrderStatus::Cancelled->value, 'payment_status' => PaymentStatus::Failed->value]);
            $order->payment()->update(['status' => PaymentStatus::Failed->value, 'response' => ['error' => $e->getMessage()]]);
            return redirect()->route('cart.index')->with('error', 'حدث خطأ في بوابة الدفع الإلكتروني: ' . $e->getMessage());
        }
    }

    public function showMockPaymentForm(\App\Models\Order $order, Request $request)
    {
        if ($order->status !== OrderStatus::Pending->value || $order->payment_status === PaymentStatus::Paid->value) {
            return redirect()->route('orders.show', $order);
        }

        $method = $request->query('method', 'card');
        return view('shop.payment_mock', compact('order', 'method'));
    }

    public function processMockPayment(\App\Models\Order $order, Request $request)
    {
        $request->validate([
            'status' => 'required|in:success,failed',
        ]);

        if ($request->status === 'success') {
            $order->update([
                'status' => OrderStatus::Processing->value,
                'payment_status' => PaymentStatus::Paid->value
            ]);
            $order->payment()->updateOrCreate([], [
                'status' => 'success',
                'transaction_id' => 'MOCK-TXN-' . strtoupper(uniqid()),
                'amount' => $order->total,
                'method' => $request->query('method', 'card'),
                'response' => ['status' => 'mocked_success']
            ]);

            // Clear the cart on successful payment
            session()->forget('cart');

            return redirect()->route('orders.show', $order)->with('success', 'تم محاكاة عملية الدفع بنجاح! شكراً لتعاملك معنا.');
        } else {
            $order->update([
                'status' => OrderStatus::Cancelled->value,
                'payment_status' => PaymentStatus::Failed->value
            ]);
            $order->payment()->updateOrCreate([], [
                'status' => 'failed',
                'response' => ['status' => 'mocked_failed']
            ]);

            return redirect()->route('cart.index')->with('error', 'تم إلغاء عملية الدفع (محاكاة). يمكنك تجربة الدفع مرة أخرى.');
        }
    }
}
