<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_key',
        'role_name',
        'permission_name',
        'notify_creator',
        'channels',
    ];

    protected $casts = [
        'channels' => 'array',
        'notify_creator' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(NotificationEvent::class, 'event_key', 'event_key');
    }
}
