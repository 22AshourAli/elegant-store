<?php

namespace App\Http\Controllers\Shop;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Enums\OrderStatus;
use App\Notifications\NewReviewAdminNotification;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        $existing = Review::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => app()->getLocale() === 'ar'
                    ? 'لقد قمت بتقييم هذا المنتج مسبقاً.'
                    : 'You have already reviewed this product.',
            ], 422);
        }

        $purchasedOrderItem = $user->orders()
            ->where('status', OrderStatus::Delivered->value)
            ->whereHas('items', fn($q) => $q->whereHas('variant', fn($v) => $v->where('product_id', $product->id)))
            ->first();

        if (!$purchasedOrderItem) {
            return response()->json([
                'message' => app()->getLocale() === 'ar'
                    ? 'التقييم متاح فقط لأصحاب الطلبات المستلمة للمنتج لضمان المصداقية.'
                    : 'Reviews are only available for customers who have purchased and received this product.',
            ], 403);
        }

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $purchasedOrderItem->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => 'pending',
        ]);

        try {
            $admins = \App\Models\User::whereIn('role', array_map(fn($r) => $r->value, UserRole::adminRoles()))->get();
            foreach ($admins as $admin) {
                $admin->notify(new NewReviewAdminNotification($review));
            }
        } catch (\Throwable $e) {
            \Log::error('Review notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'message' => app()->getLocale() === 'ar'
                ? 'تم إرسال تقييمك بنجاح وسيتم نشره بعد مراجعة الإدارة.'
                : 'Your review has been submitted successfully and will be published after admin approval.',
            'review' => $review,
        ]);
    }
}
