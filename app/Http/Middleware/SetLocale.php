<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('lang') && in_array($request->get('lang'), ['ar', 'en'])) {
            $locale = $request->get('lang');
            App::setLocale($locale);
            Session::put('locale', $locale);
            $this->saveUserLocale($request->user(), $locale);
        } elseif ($request->user() && Schema::hasColumn('users', 'locale') && $request->user()->locale) {
            App::setLocale($request->user()->locale);
            Session::put('locale', $request->user()->locale);
        } elseif (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } else {
            App::setLocale(config('app.locale', 'ar'));
        }

        return $next($request);
    }

    private function saveUserLocale($user, string $locale): void
    {
        if (!$user || !Schema::hasColumn('users', 'locale')) {
            return;
        }
        $user->updateQuietly(['locale' => $locale]);
    }
}
