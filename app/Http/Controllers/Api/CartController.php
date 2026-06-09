<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AbandonedCartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private readonly AbandonedCartService $abandonedCartService
    ) {}

    public function recover(string $token)
    {
        $cart = $this->abandonedCartService->recoverByToken($token);

        if (!$cart) {
            return response()->json(['message' => 'Invalid or expired recovery link.'], 404);
        }

        return response()->json([
            'message' => 'Cart restored successfully',
            'data' => [
                'items' => $cart->items,
                'total' => (float) $cart->total,
                'coupon_code' => $cart->coupon_code,
            ],
        ]);
    }
}
