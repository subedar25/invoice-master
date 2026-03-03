<?php

namespace App\Infrastructure\Persistence\Notification;


use App\Core\Notification\Contracts\NotificationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\User;

class EloquentNotificationRepository implements NotificationRepository
{
    public function paginateForUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return User::findOrFail($userId)
            ->notifications()
            ->latest()
            ->paginate($perPage);
    }

    public function markAsRead(int $userId, string $notificationId): void
    {
        $notification = User::findOrFail($userId)
            ->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        $notification->markAsRead();
    }

    public function markAllAsRead(int $userId): void
    {
        User::findOrFail($userId)
            ->unreadNotifications
            ->markAsRead();
    }
}
