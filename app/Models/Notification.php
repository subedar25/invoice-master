<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Notification extends DatabaseNotification
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Get the title attribute from the data array.
     */
    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->data['title'] ?? 'Notification',
        );
    }

    /**
     * Get the message attribute from the data array.
     */
    protected function message(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->data['message'] ?? '',
        );
    }

    /**
     * Get the url attribute from the data array.
     */
    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->data['url'] ?? null,
        );
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): bool
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
            return true;
        }

        return false;
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread(): bool
    {
        if (!is_null($this->read_at)) {
            $this->update(['read_at' => null]);
            return true;
        }

        return false;
    }

    /**
     * Determine if the notification has been read.
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Determine if the notification is unread.
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
