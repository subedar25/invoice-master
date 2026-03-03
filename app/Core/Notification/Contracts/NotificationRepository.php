<?php

namespace App\Core\Notification\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepository
{
    public function paginateForUser(int $userId, int $perPage = 10): LengthAwarePaginator;

    public function markAsRead(int $userId, string $notificationId): void;

    public function markAllAsRead(int $userId): void;
}
