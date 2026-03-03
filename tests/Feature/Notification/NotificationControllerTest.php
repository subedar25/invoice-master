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

        $response = $this->patch(
            route('masterapp.notifications.read', $notification->id)
        );

        $response->assertJson(['success' => true]);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }
}
