<?php

namespace Database\Seeders;

use App\Models\Season;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    public function run(): void
    {
        $seasons = [
            ['name' => 'Spring', 'code' => 'SPR', 'description' => null, 'status' => true],
            ['name' => 'Summer', 'code' => 'SUM', 'description' => null, 'status' => true],
            ['name' => 'Fall', 'code' => 'FAL', 'description' => null, 'status' => true],
            ['name' => 'Winter', 'code' => 'WIN', 'description' => null, 'status' => true],
        ];

        foreach ($seasons as $data) {
            Season::updateOrCreate(
                ['name' => $data['name']],
                [
                    'code' => $data['code'],
                    'description' => $data['description'],
                    'status' => $data['status'],
                ]
            );
        }
    }
}
