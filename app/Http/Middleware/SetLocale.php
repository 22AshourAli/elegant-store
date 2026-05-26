<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('lang') && in_array($request->get('lang'), ['ar', 'en'])) {
            $locale = $request->get('lang');
            App::setLocale($locale);
            Session::put('locale', $locale);
        } elseif (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } else {
            $default = config('app.locale', 'ar');
            App::setLocale($default);
            Session::put('locale', $default);
        }

        return $next($request);
    }
}