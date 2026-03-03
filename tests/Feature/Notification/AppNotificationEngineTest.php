<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

#[Group('notifications')]
class AppNotificationEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Admin User']);
        Role::firstOrCreate(['name' => 'User']);

        // Run notification seeders
        $this->seed([
            \Database\Seeders\NotificationEventSeeder::class,
            \Database\Seeders\NotificationRuleSeeder::class,
        ]);
    }

    #[Test]
    public function admin_receives_universal_notification_on_user_created(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole('Admin User');

        $creator = User::factory()->create();
        $creator->assignRole('User');

        $this->actingAs($creator);

        $this->post(route('masterapp.users.store'), [
            'first_name' => 'deva',
            'last_name'  => 's',
            'email'      => 'devas@example.com',
            'password'   => 'password123',
            'password_confirmation' => 'password123',
            'active' => true,
        ]);

        Notification::assertSentTo(
            $admin,
            AppNotification::class,
            function ($notification) {
                $data = $notification->toArray(null);

                return $data['event_key'] === 'user.created'
                    && $data['title'] === 'New User Created';
            }
        );
    }

    #[Test]
    public function creator_does_not_receive_own_universal_notification(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole('Admin User');

        $creator = User::factory()->create();
        $creator->assignRole('User');

        $this->actingAs($creator);

        $this->post(route('masterapp.users.store'), [
            'first_name' => 'No',
            'last_name'  => 'Notify',
            'email'      => 'no.notify@example.com',
            'password'   => 'password123',
            'password_confirmation' => 'password123',
            'active' => true,
        ]);

        Notification::assertNotSentTo(
            $creator,
            AppNotification::class
        );
    }
}
