<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PublicationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'publication_type' => 'Issue', 'parent_id' => 0],
            ['id' => 2, 'publication_type' => 'Distribution', 'parent_id' => 0],
            ['id' => 3, 'publication_type' => 'Digital Issue', 'parent_id' => 0],
            ['id' => 4, 'publication_type' => 'Rack', 'parent_id' => 2],
            ['id' => 5, 'publication_type' => 'Poster', 'parent_id' => 2],
            ['id' => 6, 'publication_type' => 'Custom', 'parent_id' => 0],
        ];

        foreach ($rows as $row) {
            DB::table('publication_types')->updateOrInsert(
                ['id' => $row['id']],
                [
                    'publication_type' => $row['publication_type'],
                    'parent_id' => $row['parent_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
