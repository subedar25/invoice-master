<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuditTestSeeder extends Seeder
{
    public function run(): void
    {
        $table = config('audit.drivers.database.table', 'audits');

        if (DB::table($table)->exists()) {
            return;
        }

        DB::table($table)->insert([
            'user_type' => null,
            'user_id' => null,
            'event' => 'created',
            'auditable_type' => 'App\\Models\\Contact',
            'auditable_id' => 1,
            'old_values' => null,
            'new_values' => json_encode([
                'name' => 'Sample Contact',
                'notes' => 'Seeded audit entry',
            ]),
            'url' => '/master-app/contacts',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Seeder',
            'tags' => 'seed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
