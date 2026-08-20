<?php

namespace App\Http\Middleware;

use App\Services\AbandonedCartService;
use App\Notifications\AbandonedCartReminder;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProcessAbandonedCarts
{
    public function handle(Request $request, Closure $next)
    {
        if (Cache::lock('abandoned_carts_processing', 60)->get()) {
            $service = app(AbandonedCartService::class);

            $service->identifyAbandonedCarts(120);

            $processed = $service->processRecovery(50);

            foreach ($processed as $item) {
                if ($item['user_id']) {
                    $user = \App\Models\User::find($item['user_id']);
                    if ($user) {
                        $user->notify(new AbandonedCartReminder(
                            $item['cart'],
                            $item['recovery_url'],
                            $item['coupon_code']
                        ));
                    }
                }
            }
        }

        return $next($request);
    }
}
