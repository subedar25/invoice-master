<?php

namespace App\Core\Notification\Services;

use App\Core\Notification\Contracts\NotificationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationService
{
    public function __construct(
        private NotificationRepository $notifications
    ) {}

    public function getUserNotifications(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->notifications->paginateForUser($userId, $perPage);
    }

    public function markAsRead(int $userId, string $notificationId): void
    {
        $this->notifications->markAsRead($userId, $notificationId);
    }

    public function markAllAsRead(int $userId): void
    {
        $this->notifications->markAllAsRead($userId);
    }
}
