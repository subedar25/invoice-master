<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TimeOffRequest;
use App\Models\User;
use Illuminate\Support\Carbon;

class TimeOffRequestSeeder extends Seeder
{
    public function run()
    {
        // Ensure there are users
        $user = User::first();
        if (!$user) {
            $this->command->info('No users found. Please seed users first.');
            return;
        }

        $users = User::all();

        foreach($users as $user) {
            // Skip if this user already has seeded time off requests
            $hasSeeded = TimeOffRequest::where('user_id', $user->id)
                ->where('notes', 'like', 'Seeded %')
                ->exists();
            if ($hasSeeded) {
                continue;
            }

            // Create a few requests for each user
            TimeOffRequest::create([
                'user_id' => $user->id,
                'start_time' => Carbon::now()->addDays(rand(1, 10)),
                'end_time' => Carbon::now()->addDays(rand(11, 20)),
                'paid' => rand(0, 1),
                'status' => 'pending',
                'notes' => 'Seeded pending request for ' . $user->first_name,
                'submitted' => 1,
                'timesheet_id' => null,
            ]);

            TimeOffRequest::create([
                'user_id' => $user->id,
                'start_time' => Carbon::now()->subDays(rand(10, 20)),
                'end_time' => Carbon::now()->subDays(rand(1, 9)),
                'paid' => 1,
                'status' => 'approved_paid',
                'notes' => 'Seeded approved request for ' . $user->first_name,
                'submitted' => 1,
                'timesheet_id' => null,
            ]);
        }
        
        $this->command->info('Time Off Requests seeded successfully.');
    }
}
