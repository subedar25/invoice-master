<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'publication_type_id' => 1, 'name' => 'Door County Living'],
            ['id' => 2, 'publication_type_id' => 1, 'name' => 'Door Wedding'],
            ['id' => 3, 'publication_type_id' => 1, 'name' => 'Peninsula Pulse'],
            ['id' => 4, 'publication_type_id' => 2, 'name' => 'Paper Boy'],
            ['id' => 5, 'publication_type_id' => 3, 'name' => 'Pulse Pick & Website'],
        ];

        foreach ($rows as $row) {
            $code = strtolower(Str::slug(trim($row['name']), '_'));
            DB::table('publications')->updateOrInsert(
                ['id' => $row['id']],
                [
                    'publication_type_id' => $row['publication_type_id'],
                    'name' => $row['name'],
                    'code' => $code,
                    'description' => null,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
