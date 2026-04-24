<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Helpers\NotificationHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('notifications')]
class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_view_only_their_notifications(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        NotificationHelper::create($user1, 'User1 Notification', 'Message 1');
        NotificationHelper::create($user2, 'User2 Notification', 'Message 2');

        $this->actingAs($user1);

        $response = $this->get(route('masterapp.notifications.index'));

        $response->assertStatus(200);
        $response->assertViewHas('notifications', function ($notifications) {
            return $notifications->count() === 1 &&
                   $notifications->first()->data['title'] === 'User1 Notification';
        });
    }

    #[Test]
    public function user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();

        NotificationHelper::create($user, 'Test Notification', 'Test Message');
        $notification = $user->notifications()->first();

        $this->actingAs($user);

        $response = $this->patchJson(
            route('masterapp.notifications.read', $notification->id)
        );

        $response->assertJson(['success' => true, 'marked' => true]);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    #[Test]
    public function user_cannot_mark_another_users_notification(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        NotificationHelper::create($user2, 'Other user notification', 'Message');
        $notification = $user2->notifications()->first();

        $this->actingAs($user1);

        $this->patchJson(route('masterapp.notifications.read', $notification->id))
            ->assertNotFound();
    }

    #[Test]
    public function system_user_sees_notifications_for_all_users(): void
    {
        $systemUser = User::factory()->create(['user_type' => 'systemuser']);
        $otherUser = User::factory()->create();

        NotificationHelper::create($systemUser, 'Mine', 'Message A');
        NotificationHelper::create($otherUser, 'Theirs', 'Message B');

        $this->actingAs($systemUser);

        $response = $this->get(route('masterapp.notifications.index'));

        $response->assertStatus(200);
        $response->assertViewHas('notifications', function ($notifications) {
            return $notifications->total() >= 2;
        });
    }

    #[Test]
    public function system_user_does_not_mark_another_users_notification_as_read(): void
    {
        $systemUser = User::factory()->create(['user_type' => 'systemuser']);
        $otherUser = User::factory()->create();

        NotificationHelper::create($otherUser, 'Theirs', 'Message');
        $notification = $otherUser->notifications()->first();

        $this->actingAs($systemUser);

        $this->patchJson(route('masterapp.notifications.read', $notification->id))
            ->assertJson(['success' => true, 'marked' => false]);

        $notification->refresh();
        $this->assertNull($notification->read_at);
    }
}
