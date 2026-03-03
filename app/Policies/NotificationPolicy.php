<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationPolicy
{
    /**
     * Determine whether the user can view notifications.
     */
    public function view(User $user): bool
    {
        return true; // All authenticated users can view their own notifications
    }

    /**
     * Determine whether the user can mark a notification as read.
     */
    public function markAsRead(User $user, DatabaseNotification $notification): bool
    {
        return $user->id === $notification->notifiable_id;
    }

    /**
     * Determine whether the user can mark all notifications as read.
     */
    public function markAllAsRead(User $user): bool
    {
        return true; // All authenticated users can mark their own notifications as read
    }
}
