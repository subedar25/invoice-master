<?php

namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;
use App\Core\Notification\Services\NotificationService;
use App\Http\Requests\MasterApp\Notification\MarkNotificationReadRequest;
use App\Http\Requests\MasterApp\Notification\MarkAllNotificationsReadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function index(NotificationService $service): View
    {
        $notifications = $service->getUserNotifications(auth()->id(), 10);

        return view('masterapp.notifications.index', compact('notifications'));
    }

    public function markAsRead(string $id, NotificationService $service): JsonResponse | RedirectResponse 
    {
        $service->markAsRead(auth()->id(), $id);

        // return response()->json(['success' => true]);
           //  If request expects JSON (AJAX)
    if (request()->expectsJson()) {
        return response()->json(['success' => true]);
    }

    //  Otherwise redirect to notifications index
    return redirect()->route('masterapp.notifications.index');
    }

    public function markAllRead(MarkAllNotificationsReadRequest $request, NotificationService $service): JsonResponse 
    {
        $service->markAllAsRead(auth()->id());

        return response()->json(['success' => true]);
    }
}
