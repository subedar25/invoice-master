<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change old_values and new_values to LONGTEXT so large content (e.g. HTML description) can be audited.
     */
    public function up(): void
    {
        $connection = config('audit.drivers.database.connection', config('database.default'));
        $prefix = DB::connection($connection)->getTablePrefix();
        $tableName = $prefix . config('audit.drivers.database.table', 'audits');
        $driver = DB::connection($connection)->getDriverName();

        if ($driver === 'mysql') {
            DB::connection($connection)->statement("ALTER TABLE `{$tableName}` MODIFY `old_values` LONGTEXT NULL, MODIFY `new_values` LONGTEXT NULL");
        }
        // SQLite/PostgreSQL: TEXT is effectively unlimited; no change needed.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = config('audit.drivers.database.connection', config('database.default'));
        $prefix = DB::connection($connection)->getTablePrefix();
        $tableName = $prefix . config('audit.drivers.database.table', 'audits');
        $driver = DB::connection($connection)->getDriverName();

        if ($driver === 'mysql') {
            DB::connection($connection)->statement("ALTER TABLE `{$tableName}` MODIFY `old_values` TEXT NULL, MODIFY `new_values` TEXT NULL");
        }
    }
};
