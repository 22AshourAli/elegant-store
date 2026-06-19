<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with('product', 'user')
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->latest()
            ->paginate(20);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function show(Review $review)
    {
        $review->load('product', 'user', 'order');

        return view('admin.reviews.show', compact('review'));
    }

    public function approve(Review $review)
    {
        $review->update(['status' => 'approved']);

        return back()->with('success', app()->getLocale() === 'ar'
            ? 'تم الموافقة على التقييم.'
            : 'Review approved.');
    }

    public function reject(Review $review)
    {
        $review->update(['status' => 'rejected']);

        return back()->with('success', app()->getLocale() === 'ar'
            ? 'تم رفض التقييم.'
            : 'Review rejected.');
    }
}
