<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_key',
        'title_template',
        'message_template',
        'url_template',
    ];

    public function rules()
    {
        return $this->hasMany(NotificationRule::class, 'event_key', 'event_key');
    }
}
