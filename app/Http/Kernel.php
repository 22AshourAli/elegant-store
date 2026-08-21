<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Global HTTP middleware stack.
     *
     * Applied to every incoming request before route matching.
     * - PreventRequestsDuringMaintenance: Returns 503 when app is in maintenance mode.
     * - ValidatePostSize: Rejects requests exceeding PHP's post_max_size limit.
     * - TrimStrings: Strips whitespace from all request inputs to prevent accidental padding.
     * - ConvertEmptyStringsToNull: Converts empty string inputs to null for cleaner DB storage.
     *
     * @var array<int, class-string>
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * Route middleware groups.
     *
     * 'web' group: Session-based routes (admin panel, shop, checkout).
     * - VerifyCsrfToken: Validates CSRF tokens on state-changing requests (POST/PUT/PATCH/DELETE).
     * - HandleMethodOverride: Enables _method hidden field for PUT/PATCH/DELETE via HTML forms.
     *
     * 'api' group: Stateless API routes with rate limiting.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\HandleMethodOverride::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SyncUserCart::class,
        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Route-level middleware aliases.
     *
     * - auth: Ensures user is authenticated.
     * - admin: Custom middleware restricting access to super_admin/manager roles.
     * - guest: Redirects authenticated users away from guest-only routes (e.g., login page).
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
    ];
}
