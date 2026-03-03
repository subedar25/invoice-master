<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RoleUpdatedNotification extends Notification
{
    public $targetUser;
    public $oldRoles;
    public $newRoles;
    public $changedBy;

    public function __construct($targetUser, $oldRoles, $newRoles, $changedBy = null)
    {
        $this->targetUser = $targetUser;
        $this->oldRoles = $oldRoles;
        $this->newRoles = $newRoles;
        $this->changedBy = $changedBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'role_update',
            'title' => 'User role updated',
            'message' => $this->targetUser->name . ' roles changed from ' . implode(',', $this->oldRoles) . ' to ' . implode(',', $this->newRoles),
            'user_id' => $this->targetUser->id,
            'changed_by' => $this->changedBy?->id ?? null,
            'url' => route('masterapp.users.edit', $this->targetUser->id)
        ];
    }
}
