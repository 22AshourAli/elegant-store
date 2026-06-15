<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    public function index(CartService $cart)
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

        // Determine shipping cost for display
        $previousOrders = auth()->user()->orders()->where('status', '!=', 'cancelled')->count();
        $shipping = ($previousOrders === 0) ? 0 : config('store.default_shipping', 30);
        $finalTotal = $total + $shipping;

        $hasActiveCoupons = \App\Models\Coupon::where('is_active', true)
            ->where(function($q) { $q->whereNull('valid_from')->orWhere('valid_from', '<=', now()); })
            ->where(function($q) { $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()); })
            ->exists();

        return view('shop.checkout', compact('cartItems', 'baseTotal', 'discount', 'total', 'appliedCoupon', 'shipping', 'finalTotal', 'hasActiveCoupons'));
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
            'payment_method' => 'required|in:cash,card,wallet',
            'phone' => ['required', 'string', 'regex:/^(01)[0-9]{9}$/', 'size:11'],
            'notes' => 'nullable|string|max:1000',
        ]);

        $cartItems = $cart->getEnrichedCart();
        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('error', 'السلة فارغة.');
        }

        try {
            $data = $request->all();
            $data['branch_id'] = 1;

            // compute subtotal, discount, and shipping from cart service
            $previousOrders = auth()->user()->orders()->where('status', '!=', 'cancelled')->count();
            $data['subtotal'] = $cart->baseTotal();
            $data['discount'] = $cart->getDiscount();
            $data['shipping_cost'] = ($previousOrders === 0) ? 0 : config('store.default_shipping', 30);

            $order = $checkout->createOrder(auth()->user(), $cartItems, $data);

            if ($request->payment_method === 'cash') {
                return redirect()->route('orders.show', $order)->with('success', 'تم تسجيل طلبك بنجاح! رقم الطلب: ' . $order->id);
            }

            return $this->initiatePaymobPayment($order, $request->payment_method, $request->phone);

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'حدثت مشكلة أثناء إتمام الطلب: ' . $e->getMessage());
        }
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
            $order->update(['status' => 'cancelled', 'payment_status' => 'failed']);
            $order->payment()->update(['status' => 'failed', 'response' => ['error' => $e->getMessage()]]);
            return redirect()->route('cart.index')->with('error', 'حدث خطأ في بوابة الدفع الإلكتروني: ' . $e->getMessage());
        }
    }

    public function showMockPaymentForm(\App\Models\Order $order, Request $request)
    {
        if ($order->status !== 'pending' || $order->payment_status === 'paid') {
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
                'status' => 'processing',
                'payment_status' => 'paid'
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
                'status' => 'cancelled',
                'payment_status' => 'failed'
            ]);
            $order->payment()->updateOrCreate([], [
                'status' => 'failed',
                'response' => ['status' => 'mocked_failed']
            ]);

            return redirect()->route('cart.index')->with('error', 'تم إلغاء عملية الدفع (محاكاة). يمكنك تجربة الدفع مرة أخرى.');
        }
    }
}
