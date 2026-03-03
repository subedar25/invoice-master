<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table has NOT NULL column 'orientations' with no default; allow inserts without it.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE issue_pricing_structure_types MODIFY COLUMN orientations MEDIUMTEXT NULL DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE issue_pricing_structure_types MODIFY COLUMN orientations MEDIUMTEXT NOT NULL");
    }
};
