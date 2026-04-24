<?php

namespace App\Core\Notification\Services;

use App\Core\Notification\Contracts\NotificationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationService
{
    public function __construct(
        private NotificationRepository $notifications
    ) {}

    public function getUserNotifications(int $userId, int $perPage = 10, ?int $organizationId = null, bool $systemUserGlobalFeed = false): LengthAwarePaginator
    {
        return $this->notifications->paginateForUser($userId, $perPage, $organizationId, $systemUserGlobalFeed);
    }

    /**
     * @return array{notification: \App\Models\Notification, marked: bool}
     */
    public function markAsRead(int $userId, string $notificationId, ?int $organizationId = null, bool $isSystemUser = false): array
    {
        return $this->notifications->markAsRead($userId, $notificationId, $organizationId, $isSystemUser);
    }

    public function markAllAsRead(int $userId, ?int $organizationId = null, bool $isSystemUser = false): void
    {
        $this->notifications->markAllAsRead($userId, $organizationId, $isSystemUser);
    }
}
