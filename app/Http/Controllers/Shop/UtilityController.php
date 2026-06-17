<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UtilityController extends Controller
{
    public function sitemap()
    {
        $xml = Cache::remember('sitemap_xml', 86400, function () {
            $categories = Category::where('is_active', true)->get(['id', 'slug']);
            $products   = Product::where('is_active', true)->get(['id', 'slug']);
            return view('sitemap', compact('categories', 'products'))->render();
        });
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function dashboard()
    {
        if (auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isManager())) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('home');
    }

    public function langSwitch($locale)
    {
        if (in_array($locale, ['ar', 'en'])) {
            session()->put('locale', $locale);
        }
        return redirect()->back();
    }

    public function cartCount(CartService $cart)
    {
        return response()->json(['count' => $cart->count()]);
    }

    public function wishlistCount()
    {
        return response()->json(['count' => auth()->user()->wishlist()->count()]);
    }
}
