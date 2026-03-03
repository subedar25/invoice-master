<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewUserNotification extends Notification
{
    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'notification_type' => 'new_user',
            'title' => 'New User Created',
            'message' => $this->user->first_name . ' ' . $this->user->last_name . ' was added.',
            'user_id' => $this->user->id,
            'url' => route('masterapp.users.index', $this->user->id),
        ];
    }
}
