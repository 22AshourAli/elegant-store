<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function toggle(Product $product, Request $request)
    {
        $user = auth()->user();
        
        // Check if already in wishlist
        $exists = $user->wishlist()->where('product_id', $product->id)->exists();

        if ($exists) {
            $user->wishlist()->detach($product->id);
            $added = false;
            $message = 'تم الإزالة من قائمة الأمنيات';
        } else {
            $user->wishlist()->attach($product->id);
            $added = true;
            $message = 'تم الإضافة إلى قائمة الأمنيات';
        }

        return response()->json([
            'added' => $added,
            'message' => $message
        ]);
    }
}
