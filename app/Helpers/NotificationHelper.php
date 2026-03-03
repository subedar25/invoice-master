<?php

namespace App\Helpers;

use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Notification;

class NotificationHelper
{
    /**
     * Create a notification for a user
     *
     * @param User $user
     * @param string $title
     * @param string $message
     * @param string|null $url
     * @return void
     */
    public static function create(User $user, string $title, string $message, ?string $url = null): void
    {
        $data = [
            'title' => $title,
            'message' => $message,
            'url' => $url,
        ];

        $user->notify(new GeneralNotification($data));
    }

    /**
     * Create notifications for multiple users
     *
     * @param array $users
     * @param string $title
     * @param string $message
     * @param string|null $url
     * @return void
     */
    public static function createForUsers(array $users, string $title, string $message, ?string $url = null): void
    {
        $data = [
            'title' => $title,
            'message' => $message,
            'url' => $url,
        ];

        Notification::send($users, new GeneralNotification($data));
    }

    /**
     * Create a notification with custom data
     *
     * @param User $user
     * @param array $data
     * @return void
     */
    public static function createWithData(User $user, array $data): void
    {
        $user->notify(new GeneralNotification($data));
    }
}
