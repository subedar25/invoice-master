<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            'Available',
            'Available - Lunch',
            'Available - Out of Office',
            'Available - Remote',
            'Do Not Disturb',
            'Lunch',
            'Not Available',
        ];

        foreach ($statuses as $status) {
            DB::table('user_statuses')->updateOrInsert(
                ['label' => $status], 
                ['label' => $status]
            );
        }
    }
}
