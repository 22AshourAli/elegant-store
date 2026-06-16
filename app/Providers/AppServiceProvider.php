<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        if (app()->environment('production') && env('R2_ACCESS_KEY_ID')) {
            config()->set('filesystems.disks.public', [
                'driver' => 's3',
                'key' => env('R2_ACCESS_KEY_ID'),
                'secret' => env('R2_SECRET_ACCESS_KEY'),
                'region' => 'auto',
                'bucket' => env('R2_BUCKET', 'elegant-store'),
                'url' => env('R2_PUBLIC_URL'),
                'endpoint' => env('R2_ENDPOINT'),
                'use_path_style_endpoint' => true,
                'throw' => false,
                'report' => false,
            ]);
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) || isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            \Illuminate\Http\Request::setTrustedProxies(
                [$_SERVER['REMOTE_ADDR']],
                \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
            );
        }

        View::composer('layouts.store', function ($view) {
            $navbarCategories = Cache::remember('navbar_categories', 3600, function () {
                return Category::where('is_active', true)
                    ->whereNull('parent_id')
                    ->with('children')
                    ->get();
            });
            $view->with('navbarCategories', $navbarCategories);
        });
    }
}
