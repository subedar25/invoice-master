<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'lunch' to timesheets.clock_in_mode enum so shift can stay open during lunch.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE timesheets MODIFY COLUMN clock_in_mode ENUM('office', 'remote', 'out_of_office', 'do_not_disturb', 'lunch') NULL DEFAULT 'office'");
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE timesheets MODIFY COLUMN clock_in_mode ENUM('office', 'remote', 'out_of_office', 'do_not_disturb') NULL DEFAULT 'office'");
    }
};
