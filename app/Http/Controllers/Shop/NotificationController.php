<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(20);
        
        // Mark all as read
        auth()->user()->unreadNotifications->markAsRead();

        return view('shop.notifications.index', compact('notifications'));
    }
}
