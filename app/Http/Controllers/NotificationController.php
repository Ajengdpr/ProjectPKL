<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function index(Request $request)
    {
        $user  = $request->user();

        $items = $user->notifications()->latest()->paginate(20);

        if ($user->unreadNotifications->isNotEmpty()) {
            $user->unreadNotifications->markAsRead();
        }

        return view('notifications', compact('items'));
    }
}