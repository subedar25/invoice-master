<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // public function index(Request $request)
    // {
    //     $user = $request->user();
   
    //     $notifications = $user->notifications()->latest()->take(10)->get();

    //     return response()->json([
    //         'unread_count' => $user->unreadNotifications->count(),
    //         'notifications' => $notifications
    //     ]);
    // }
// Show all notifications
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(10);
        return view('Notification.index', compact('notifications'));
    }
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
    public function allNotifications()
{
    $notifications = auth()->user()->notifications()->paginate(20);
    return view('notification.index', compact('notifications'));
}

}
