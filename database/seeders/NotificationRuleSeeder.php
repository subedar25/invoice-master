<?php

namespace Database\Seeders;

use App\Models\NotificationRule;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class NotificationRuleSeeder extends Seeder
{
    /** Roles that receive time-off (and other) event notifications. */
    private const NOTIFICATION_ROLES = ['Admin User', 'Super Admin'];

    public function run(): void
    {
        $roles = Role::pluck('name')->toArray();

        $rules = [
            // User CRUD
            ['event_key' => 'user.created'],
            ['event_key' => 'user.updated'],
            ['event_key' => 'user.deleted'],

            // Role
            ['event_key' => 'role.updated'],

            // Timesheet
            ['event_key' => 'timesheet.created'],
            ['event_key' => 'timesheet.updated'],
            ['event_key' => 'timesheet.deleted'],

            // Time off Requests
            ['event_key' => 'timeoff.created'],
            ['event_key' => 'timeoff.updated'],
            ['event_key' => 'timeoff.deleted'],

            //Location
            ['event_key' => 'location.created'],
            ['event_key' => 'location.updated'],
            ['event_key' => 'location.deleted'],
        ];

        foreach (self::NOTIFICATION_ROLES as $roleName) {
            if (!in_array($roleName, $roles)) {
                continue;
            }
            foreach ($rules as $rule) {
                NotificationRule::updateOrCreate(
                    [
                        'event_key' => $rule['event_key'],
                        'role_name' => $roleName,
                    ],
                    [
                        'notify_creator' => false,
                        'channels' => ['database'],
                    ]
                );
            }
        }

        // Notify requestor (no role needed)
        foreach (['timeoff.approved', 'timeoff.rejected'] as $eventKey) {
            NotificationRule::updateOrCreate(
                [
                    'event_key' => $eventKey,
                    'role_name' => null,
                    'permission_name' => null,
                ],
                [
                    'notify_creator' => false,
                    'channels' => ['database'],
                ]
            );
        }
    }
}
