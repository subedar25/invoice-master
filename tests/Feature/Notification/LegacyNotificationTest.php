<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Notifications\NewUserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

#[Group('notifications')]
class LegacyNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Admin User']);
        Role::firstOrCreate(['name' => 'User']);
    }

    #[Test]
    public function admin_user_receives_notification_when_user_is_created(): void
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
            'password'   => 'password',
            'password_confirmation' => 'password',
            'active' => true,
        ]);

        Notification::assertSentTo($admin, NewUserNotification::class);
        Notification::assertNotSentTo($creator, NewUserNotification::class);
    }
}
