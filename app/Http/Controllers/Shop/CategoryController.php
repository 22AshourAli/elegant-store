<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;

class CategoryController extends Controller
{
    public function show($slug)
    {
        $category = Category::where('slug', $slug)->with('children')->firstOrFail();
        
        $categoryIds = [$category->id];
        if ($category->children->count() > 0) {
            $categoryIds = array_merge($categoryIds, $category->children->pluck('id')->toArray());
        }

        $query = Product::whereIn('category_id', $categoryIds)
            ->active()
            ->with('media', 'variants');

        // تطبيق فلتر المخزون
        if (request()->has('in_stock')) {
            $query->whereHas('variants', function ($q) {
                $q->where('stock', '>', 0);
            });
        }

        // تطبيق الترتيب
        $sort = request('sort', 'latest');
        if ($sort === 'price_asc') {
            $query->orderByRaw('COALESCE(sale_price, base_price) ASC');
        } elseif ($sort === 'price_desc') {
            $query->orderByRaw('COALESCE(sale_price, base_price) DESC');
        } else {
            $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();

        // SEO
        \SEOMeta::setTitle($category->name . ' - Elegant Store');
        \SEOMeta::setDescription('تسوق أحدث منتجات ' . $category->name . ' بأفضل الأسعار.');
        \OpenGraph::setTitle($category->name . ' - Elegant Store');

        return view('shop.category', compact('category', 'products'));
    }
}
