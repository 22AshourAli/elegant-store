<?php

namespace App\Http\Controllers\Shop;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymobWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Paymob Webhook Received', $request->all());

        $hmac = $request->query('hmac');
        $data = $request->all();

        // Calculate HMAC to verify request authenticity
        if ($this->verifyHmac($data, $hmac)) {
            $transaction = $data['obj'] ?? null;
            if ($transaction) {
                $orderId = $transaction['order']['merchant_order_id'] ?? null;
                $order = Order::find($orderId);

                if ($order) {
                    if ($transaction['success'] == true) {
                        $order->update([
                            'payment_status' => PaymentStatus::Paid->value,
                            'status' => OrderStatus::Confirmed->value
                        ]);
                        $order->payment()->update([
                            'status' => PaymentStatus::Paid->value,
                            'transaction_id' => $transaction['id'],
                            'response' => $transaction
                        ]);
                    } else {
                        $order->update([
                            'payment_status' => PaymentStatus::Failed->value,
                            'status' => OrderStatus::Cancelled->value
                        ]);
                        $order->payment()->update([
                            'status' => PaymentStatus::Failed->value,
                            'response' => $transaction
                        ]);
                    }
                }
            }
            return response()->json(['status' => 'success']);
        }

        Log::warning('Paymob Webhook HMAC verification failed');
        return response()->json(['status' => 'unauthorized'], 401);
    }

    private function verifyHmac($data, $hmac)
    {
        if (empty($hmac)) {
            return false;
        }

        // Paymob HMAC verification keys in order
        $hmacKeys = [
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_voided',
            'is_refunded',
            'is_standalone_payment',
            'order.id', // mapped to order_id in nested
            'owner',
            'pending',
            'source_data.pan',
            'source_data.sub_type',
            'source_data.type',
            'success'
        ];

        $concatString = '';
        foreach ($hmacKeys as $key) {
            $value = $this->getNestedVal($data['obj'] ?? [], $key);
            
            // convert boolean to string representations
            if (is_bool($value)) {
                $concatString .= $value ? 'true' : 'false';
            } else {
                $concatString .= $value;
            }
        }

        $secret = config('services.paymob.secret_key');
        $calculatedHmac = hash_hmac('sha512', $concatString, $secret);

        return hash_equals($calculatedHmac, $hmac);
    }

    private function getNestedVal($array, $key)
    {
        $keys = explode('.', $key);
        foreach ($keys as $k) {
            if (is_array($array) && array_key_exists($k, $array)) {
                $array = $array[$k];
            } else {
                return null;
            }
        }
        return $array;
    }
}
