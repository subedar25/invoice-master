<?php

namespace App\Services\NotificationUniverse;

use App\Models\NotificationEvent;
use App\Models\NotificationRule;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\Model;

class NotificationEngine
{
    public function notify(string $eventKey, ?Model $model = null, ?User $actor = null): void
    {
        $event = NotificationEvent::where('event_key', $eventKey)->first();
        if (!$event) {
            return;
        }

            $rules = NotificationRule::where('event_key', $eventKey)
            ->where('is_active', true)
            ->get();

        if ($rules->isEmpty()) {
            return;
        }

        $recipients = collect();


        //  SPECIAL EVENT HANDLING

        // ROLE UPDATED to ONLY affected user
        if ($eventKey === 'role.updated' && $model instanceof User) {
            $recipients->push($model);
            \Log::debug("NotificationEngine: Role update - recipient is affected user", ['user_id' => $model->id]);
        }

        // USER CRUD to notify admins + super admins
        elseif (in_array($eventKey, ['user.created', 'user.updated', 'user.deleted'])) {
            $recipients = $this->getAdminAndSystemAdminUsers();
            \Log::debug("NotificationEngine: User CRUD - recipients are admins", ['admin_count' => $recipients->count()]);
        }

        // TIMESHEET EVENTS to notify admins + super admins + timesheet owner
        elseif (str_starts_with($eventKey, 'timesheet.') && $model && isset($model->user_id)) {
            $recipients = $this->getAdminAndSystemAdminUsers();
            $recipients->push(User::find($model->user_id));
            \Log::debug("NotificationEngine: Timesheet event - recipients are admins + owner", [
                'admin_count' => $this->getAdminAndSystemAdminUsers()->count(),
                'owner_id' => $model->user_id,
            ]);
        }

        // TIMEOFF EVENTS
        elseif (str_starts_with($eventKey, 'timeoff.')) {

            // Approved / rejected to notify requestor only
            if (in_array($eventKey, ['timeoff.approved', 'timeoff.rejected']) && $model && isset($model->user_id)) {
                $recipients->push(User::find($model->user_id));
                \Log::debug("NotificationEngine: Timeoff approved/rejected - recipient is requestor", ['user_id' => $model->user_id]);
            }
            // Created / updated / deleted: notify users with roles that have a rule for this event (Admin User + System Admin)
            else {
                $recipients = $this->getTimeOffRecipientsFromRules($rules);
                \Log::debug("NotificationEngine: Timeoff created/updated/deleted - recipients from rules", ['count' => $recipients->count(), 'recipient_ids' => $recipients->pluck('id')->toArray()]);
            }
        }
        //   APPLY RULE FILTERS
        foreach ($rules as $rule) {

            // Remove actor if notify_creator = false
            if (!$rule->notify_creator && $actor) {
                $recipients = $recipients->reject(fn ($u) => $u->id === $actor->id);
                \Log::debug("NotificationEngine: Applied rule filter - removed actor", [
                    'rule_id' => $rule->id,
                    'notify_creator' => $rule->notify_creator,
                    'actor_id' => $actor->id,
                ]);
            }
        }

        $recipients = $recipients->filter()->unique('id');

        \Log::debug("NotificationEngine: Final recipient count", [
            'eventKey' => $eventKey,
            'recipient_count' => $recipients->count(),
            'recipient_ids' => $recipients->pluck('id')->toArray(),
        ]);

        if ($recipients->isEmpty()) {
            \Log::warning('Notification skipped (no recipients)', compact('eventKey'));
            return;
        }

        $data = $this->buildNotificationData($event, $model, $actor);

        Notification::send($recipients, new AppNotification($data));
    }

    private function buildNotificationData(NotificationEvent $event, ?Model $model, ?User $actor): array
    {
        return [
            'event_key' => $event->event_key,
            'title'     => $this->replace($event->title_template, $model, $actor),
            'message'   => $this->replace($event->message_template, $model, $actor),
            'url'       => $event->url_template
                ? $this->replace($event->url_template, $model, $actor)
                : null,
        ];
    }

    /**
     * Users with role "Admin User" or "System Admin" (exact names from seeders).
     */
    private function getAdminAndSystemAdminUsers(): \Illuminate\Support\Collection
    {
        return User::role(['Admin User', 'System Admin'])->get();
    }

    /**
     * For timeoff events: get recipients from notification rules (users with any role that has a rule for this event).
     * Ensures both Admin User and System Admin receive time-off notifications when rules exist for both roles.
     */
    private function getTimeOffRecipientsFromRules(\Illuminate\Support\Collection $rules): \Illuminate\Support\Collection
    {
        $roleNames = $rules->pluck('role_name')->filter()->unique()->values()->toArray();
        if (empty($roleNames)) {
            return $this->getAdminAndSystemAdminUsers();
        }
        return User::role($roleNames)->get();
    }

    private function replace(string $text, ?Model $model, ?User $actor): string
    {
        return preg_replace_callback('/\{([^}]+)\}/', function ($m) use ($model, $actor) {
            [$root, $field] = array_pad(explode('.', $m[1]), 2, null);

            return match ($root) {
                'actor' => $actor?->{$field} ?? '',
                'model' => $model?->{$field} ?? '',
                default => $m[0],
            };
        }, $text);
    }
}
