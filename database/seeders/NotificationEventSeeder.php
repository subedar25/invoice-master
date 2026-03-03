<?php

namespace Database\Seeders;

use App\Models\NotificationEvent;
use Illuminate\Database\Seeder;

class NotificationEventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            // User CRUD
            [
                'event_key' => 'user.created',
                'title_template' => 'New User Created',
                'message_template' => 'A new user {model.first_name} {model.last_name} has been created by {actor.first_name} {actor.last_name}.',
                'url_template' => '/users/{model.id}',
            ],
            [
                'event_key' => 'user.updated',
                'title_template' => 'User Updated',
                'message_template' => 'User {model.first_name} {model.last_name} has been updated by {actor.first_name} {actor.last_name}.',
                'url_template' => '/users/{model.id}',
            ],
            [
                'event_key' => 'user.deleted',
                'title_template' => 'User Deleted',
                'message_template' => 'User {model.first_name} {model.last_name} has been deleted by {actor.name}.',
                'url_template' => null,
            ],

            // Role
            [
                'event_key' => 'role.updated',
                'title_template' => 'User Role Updated',
                'message_template' => 'Role of user {model.first_name} {model.last_name} was updated by {actor.first_name} {actor.last_name}.',
                'url_template' => '/users/{model.id}',
            ],

            // Timesheet
            [
                'event_key' => 'timesheet.created',
                'title_template' => 'New Timesheet Entry',
                'message_template' => 'A new timesheet entry for {model.date} has been created by {actor.first_name} {actor.last_name}.',
                'url_template' => '/timesheets/{model.id}',
            ],
            [
                'event_key' => 'timesheet.updated',
                'title_template' => 'Timesheet Updated',
                'message_template' => 'Timesheet entry for {model.date} has been updated by {actor.name}.',
                'url_template' => '/timesheets/{model.id}',
            ],
            [
                'event_key' => 'timesheet.deleted',
                'title_template' => 'Timesheet Deleted',
                'message_template' => 'Timesheet entry for {model.date} has been deleted by {actor.name}.',
                'url_template' => null,
            ],

            // Time off Requests
            [
                'event_key' => 'timeoff.created',
                'title_template' => 'New Time Off Request',
                'message_template' => 'A new time off request from {model.start_time} to {model.end_time} has been submitted by {actor.name}.',
                'url_template' => '/time-off/{model.id}',
            ],
            [
                'event_key' => 'timeoff.updated',
                'title_template' => 'Time Off Request Updated',
                'message_template' => 'Time off request from {model.start_time} to {model.end_time} has been updated by {actor.name}.',
                'url_template' => '/time-off/{model.id}',
            ],
            [
                'event_key' => 'timeoff.approved',
                'title_template' => 'Time Off Request Approved',
                'message_template' => 'Your time off request from {model.start_time} to {model.end_time} has been approved.',
                'url_template' => '/time-off/{model.id}',
            ],
            [
                'event_key' => 'timeoff.rejected',
                'title_template' => 'Time Off Request Rejected',
                'message_template' => 'Your time off request from {model.start_time} to {model.end_time} has been rejected.',
                'url_template' => '/time-off/{model.id}',
            ],
            [
                'event_key' => 'timeoff.deleted',
                'title_template' => 'Time Off Request Deleted',
                'message_template' => 'Time off request from {model.start_time} to {model.end_time} has been deleted by {actor.name}.',
                'url_template' => null,
            ],
            //Locatiion
            [
                'event_key' => 'location.created',
                'title_template' => 'New Location Created',
                'message_template' => 'A new location {model.name} has been created by {actor.first_name} {actor.last_name}.',
                'url_template' => '/locations/{model.id}',
            ],
            [
                'event_key' => 'location.updated',
                'title_template' => 'Location Updated',
                'message_template' => 'Location {model.name} has been updated by {actor.first_name} {actor.last_name}.',
                'url_template' => '/locations/{model.id}',
            ],
            [
                'event_key' => 'location.deleted',
                'title_template' => 'Location Deleted',
                'message_template' => 'Location {model.name} has been deleted by {actor.name}.',
                'url_template' => null,
            ],
        ];

        foreach ($events as $event) {
            NotificationEvent::updateOrCreate(
                ['event_key' => $event['event_key']],
                $event
            );
        }
    }
}
