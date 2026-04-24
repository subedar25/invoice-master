<?php

namespace App\Core\Notification\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepository
{
    public function paginateForUser(int $userId, int $perPage = 10, ?int $organizationId = null, bool $systemUserGlobalFeed = false): LengthAwarePaginator;

    /**
     * @return array{notification: \App\Models\Notification, marked: bool}
     */
    public function markAsRead(int $userId, string $notificationId, ?int $organizationId = null, bool $isSystemUser = false): array;

    public function markAllAsRead(int $userId, ?int $organizationId = null, bool $isSystemUser = false): void;
}
