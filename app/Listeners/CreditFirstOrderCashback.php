<?php

namespace App\Listeners;

use App\Events\OrderDelivered;
use App\Models\CustomerWallet;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class CreditFirstOrderCashback implements ShouldQueue
{
    public function handle(OrderDelivered $event): void
    {
        $order = $event->order;
        $user = $order->user;

        $deliveredCount = Order::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->count();

        if ($deliveredCount !== 1) {
            return;
        }

        DB::transaction(function () use ($user) {
            CustomerWallet::updateOrCreate(
                ['user_id' => $user->id],
                ['balance' => DB::raw('COALESCE(balance, 0) + 30.00')]
            );

            $user->notifications()->create([
                'type' => 'cashback',
                'data' => [
                    'message' => 'تهانينا! تم إضافة 30 ج.م كمكافأة لأول طلب لك! يمكنك استخدامها في طلبك القادم.',
                ],
            ]);
        });
    }
}
