<?php

namespace App\Helpers;

use App\Services\NotificationUniverse\NotificationEngine;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AppNotification
{
    /**
     * Trigger a universal notification event
     *
     * @param string $eventKey
     * @param Model|null $model
     * @param User|null $actor
     * @return void
     */
    public static function notify_event(string $eventKey, ?Model $model = null, ?User $actor = null): void
    {
        \Log::debug("UniversalNotification::notify_event called", [
            'eventKey' => $eventKey,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->getKey() : null,
            'actor_id' => $actor ? $actor->id : null,
        ]);

        $engine = new NotificationEngine();
        $engine->notify($eventKey, $model, $actor);
    }
}
