<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $query = Product::with('media', 'variants')->where('is_active', true);

        $hasFilters = request()->filled('category_id') || request()->filled('min_price') || request()->filled('max_price') || request()->filled('sort');

        if (request('category_id')) {
            $query->where('category_id', request('category_id'));
        }

        if (request('min_price')) {
            $query->where('base_price', '>=', request('min_price'));
        }
        if (request('max_price')) {
            $query->where('base_price', '<=', request('max_price'));
        }

        switch (request('sort')) {
            case 'price_asc':
                $query->orderBy('base_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('base_price', 'desc');
                break;
            case 'name_az':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->latest();
                break;
        }

        if (!$hasFilters) {
            $query->where('featured', true);
        }

        $products = $query->paginate(12)->appends(request()->query());

        if (request()->ajax() || request()->has('ajax')) {
            $html = view('shop.partials.product-grid', compact('products'))->render();
            return response()->json(['html' => $html]);
        }

        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->get();

        $slides = [
            [
                'title' => __('global.slide_1_title'),
                'description' => __('global.slide_1_desc'),
                'cta' => __('global.shop_now'),
                'link' => route('shop.category', 'men-clothing'),
                'gradient' => 'from-indigo-600 to-purple-700 dark:from-gray-800 dark:to-gray-900',
            ],
            [
                'title' => __('global.slide_2_title'),
                'description' => __('global.slide_2_desc'),
                'cta' => __('global.shop_now'),
                'link' => route('shop.category', 'men-pants'),
                'gradient' => 'from-rose-600 to-orange-600 dark:from-gray-800 dark:to-gray-900',
            ],
            [
                'title' => __('global.slide_3_title'),
                'description' => __('global.slide_3_desc'),
                'cta' => __('global.learn_more'),
                'link' => '#featured',
                'gradient' => 'from-emerald-600 to-teal-600 dark:from-gray-800 dark:to-gray-900',
            ],
        ];

        // SEO
        \SEOMeta::setTitle('متجر إليجانت - أفضل الملابس');
        \SEOMeta::setDescription('تسوق أحدث صيحات الموضة الرجالية. جودة عالية وأسعار منافسة.');
        \OpenGraph::setTitle('Elegant Store');
        \OpenGraph::setDescription('تسوق أحدث صيحات الموضة الرجالية. جودة عالية وأسعار منافسة.');

        return view('shop.home', compact('products', 'categories', 'slides'));
    }
}
