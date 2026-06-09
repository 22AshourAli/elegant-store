<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
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
