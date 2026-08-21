<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DomainLockMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedDomains = env('APP_ALLOWED_DOMAINS');

        if (empty($allowedDomains)) {
            return $next($request);
        }

        $host = $request->getHost();

        $domains = array_map('trim', explode(',', $allowedDomains));

        if (!in_array($host, $domains)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Domain not authorized.',
                    'message' => 'This application is not configured to run on this domain.',
                ], 403);
            }

            return response()->view('errors.domain', [], 403);
        }

        return $next($request);
    }
}
