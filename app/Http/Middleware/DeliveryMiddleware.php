<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DeliveryMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role === \App\Enums\UserRole::Delivery->value) {
            return $next($request);
        }
        abort(403, 'Unauthorized');
    }
}
