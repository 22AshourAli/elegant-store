<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class LicenseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $licenseKey = env('APP_LICENSE_KEY');
        $appName = env('APP_NAME', 'elegant-store');

        if (empty($licenseKey)) {
            return $next($request);
        }

        $validLicense = hash_hmac('sha256', $appName . config('app.key'), 'elegant-store-license');

        if ($licenseKey !== $validLicense) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Invalid license key.',
                    'message' => 'This application requires a valid license to operate.',
                ], 403);
            }

            return response()->view('errors.license', [], 403);
        }

        return $next($request);
    }
}
