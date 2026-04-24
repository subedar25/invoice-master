<?php

namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;
use App\Core\Notification\Services\NotificationService;
use App\Http\Requests\MasterApp\Notification\MarkAllNotificationsReadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function index(NotificationService $service): View
    {
        $user = auth()->user();
        $isSystemUser = $user->isSystemUser();
        $currentOrganizationId = (int) session('current_organization_id', 0);
        $organizationScope = $currentOrganizationId > 0 ? $currentOrganizationId : null;
        $notifications = $service->getUserNotifications(
            $user->id,
            10,
            $organizationScope,
            $isSystemUser
        );
        $unreadCount = $user
            ->notifications()
            ->when(! $isSystemUser, fn ($q) => $q->forOrganization($organizationScope))
            ->whereNull('read_at')
            ->count();

        return view('masterapp.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead(string $id, NotificationService $service): JsonResponse | RedirectResponse 
    {
        $user = auth()->user();
        $isSystemUser = $user->isSystemUser();
        $currentOrganizationId = (int) session('current_organization_id', 0);
        $organizationScope = $currentOrganizationId > 0 ? $currentOrganizationId : null;
        ['notification' => $notification, 'marked' => $marked] = $service->markAsRead(
            $user->id,
            $id,
            $organizationScope,
            $isSystemUser
        );
        $targetUrl = $notification->data['url'] ?? null;

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'marked' => $marked,
                'url' => $targetUrl,
            ]);
        }

        if (! empty($targetUrl)) {
            return redirect()->to($targetUrl);
        }

        return redirect()->route('masterapp.notifications.index');
    }

    public function markAllRead(MarkAllNotificationsReadRequest $request, NotificationService $service): JsonResponse 
    {
        $user = auth()->user();
        $isSystemUser = $user->isSystemUser();
        $currentOrganizationId = (int) session('current_organization_id', 0);
        $organizationScope = $currentOrganizationId > 0 ? $currentOrganizationId : null;
        $service->markAllAsRead($user->id, $organizationScope, $isSystemUser);

        return response()->json(['success' => true]);
    }
}
