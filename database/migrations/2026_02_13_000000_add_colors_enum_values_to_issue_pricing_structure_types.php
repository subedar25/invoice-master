<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add 'Color & B&W' and 'optional' to the colors enum.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `issue_pricing_structure_types` MODIFY COLUMN `colors` ENUM('black', 'color', 'both', 'Color & B&W', 'optional') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'both'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Before reverting, ensure no rows use the new values (or they would be invalid)
        DB::statement("ALTER TABLE `issue_pricing_structure_types` MODIFY COLUMN `colors` ENUM('black', 'color', 'both') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'both'");
    }
};
