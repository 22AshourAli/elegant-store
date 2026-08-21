<?php

namespace App\Http\Middleware;

use App\Services\CartService;
use Closure;
use Illuminate\Http\Request;

class SyncUserCart
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && !$request->ajax() && !$request->expectsJson()) {
            $userId = auth()->id();
            $lastSync = session('cart_last_sync', 0);
            if (now()->timestamp - $lastSync > 60) {
                app(CartService::class)->syncFromDb();
                session(['cart_last_sync' => now()->timestamp]);
            }
        }
        return $next($request);
    }
}
