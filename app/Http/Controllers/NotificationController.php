<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // pastikan harus login
    }

    public function index(Request $request)
    {
        $user  = $request->user();

        // Ambil notifikasi terbaru (paginate biar ringan)
        $items = $user->notifications()->latest()->paginate(20);

        // Tandai semua unread jadi read ketika halaman dibuka
        if ($user->unreadNotifications->isNotEmpty()) {
            $user->unreadNotifications->markAsRead();
        }

        return view('notifications', compact('items'));
    }
}
