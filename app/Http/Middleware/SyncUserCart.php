<?php

namespace App\Http\Middleware;

use App\Services\CartService;
use Closure;
use Illuminate\Http\Request;

class SyncUserCart
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            app(CartService::class)->syncFromDb();
        }
        return $next($request);
    }
}
