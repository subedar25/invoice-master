<?php

namespace App\Infrastructure\Persistence\Notification;


use App\Core\Notification\Contracts\NotificationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EloquentNotificationRepository implements NotificationRepository
{
    public function paginateForUser(int $userId, int $perPage = 10, ?int $organizationId = null, bool $systemUserGlobalFeed = false): LengthAwarePaginator
    {
        if ($systemUserGlobalFeed) {
            return Notification::query()
                ->where('notifiable_type', User::class)
                ->with(['notifiable:id,first_name,last_name,email'])
                ->latest()
                ->paginate($perPage);
        }

        return User::findOrFail($userId)
            ->notifications()
            ->forOrganization($organizationId)
            ->latest()
            ->paginate($perPage);
    }

    public function markAsRead(int $userId, string $notificationId, ?int $organizationId = null, bool $isSystemUser = false): array
    {
        $notification = Notification::query()->where('id', $notificationId)->firstOrFail();

        if ((int) $notification->notifiable_id !== $userId) {
            if (! $isSystemUser) {
                throw (new ModelNotFoundException())->setModel(Notification::class, [$notificationId]);
            }

            return ['notification' => $notification, 'marked' => false];
        }

        if (! $isSystemUser && $organizationId !== null && $organizationId > 0) {
            if ((int) ($notification->organization_id ?? 0) !== (int) $organizationId) {
                throw (new ModelNotFoundException())->setModel(Notification::class, [$notificationId]);
            }
        }

        $wasUnread = $notification->read_at === null;
        if ($wasUnread) {
            $notification->markAsRead();
            $notification->refresh();
        }

        return ['notification' => $notification, 'marked' => $wasUnread];
    }

    public function markAllAsRead(int $userId, ?int $organizationId = null, bool $isSystemUser = false): void
    {
        $query = User::findOrFail($userId)
            ->notifications()
            ->whereNull('read_at');

        if (! $isSystemUser) {
            $query->forOrganization($organizationId);
        }

        $query->update(['read_at' => now()]);
    }
}
