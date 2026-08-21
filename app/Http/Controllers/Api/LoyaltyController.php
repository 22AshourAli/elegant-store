<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerWallet;
use App\Models\LoyaltyPointsLog;
use App\Models\User;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function __construct(
        private readonly LoyaltyService $loyaltyService
    ) {}

    public function balance(User $user)
    {
        $wallet = CustomerWallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return response()->json([
                'loyalty_points' => 0,
                'lifetime_spent' => 0,
            ]);
        }

        return response()->json([
            'loyalty_points' => $wallet->loyalty_points,
            'lifetime_spent' => (float) $wallet->lifetime_spent,
            'can_redeem' => $wallet->loyalty_points >= 10,
            'points_value_egp' => $wallet->loyalty_points / LoyaltyService::POINTS_PER_CURRENCY,
        ]);
    }

    public function redeem(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer|min:10',
            'order_id' => 'required|exists:orders,id',
        ]);

        try {
            $discount = $this->loyaltyService->redeemPoints(
                $request->integer('user_id'),
                $request->integer('points'),
                $request->integer('order_id')
            );

            return response()->json([
                'message' => 'Points redeemed successfully',
                'discount_amount' => $discount,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function history(User $user)
    {
        $logs = LoyaltyPointsLog::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($logs);
    }
}
