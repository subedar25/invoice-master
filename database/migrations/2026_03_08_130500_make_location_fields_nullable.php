<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `locations` MODIFY `address` TEXT NULL');
        DB::statement('ALTER TABLE `locations` MODIFY `city` VARCHAR(255) NULL');
        DB::statement('ALTER TABLE `locations` MODIFY `state` VARCHAR(255) NULL');
        DB::statement('ALTER TABLE `locations` MODIFY `country` VARCHAR(255) NULL');
        DB::statement('ALTER TABLE `locations` MODIFY `postal_code` VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `locations` MODIFY `address` TEXT NOT NULL');
        DB::statement('ALTER TABLE `locations` MODIFY `city` VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE `locations` MODIFY `state` VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE `locations` MODIFY `country` VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE `locations` MODIFY `postal_code` VARCHAR(255) NOT NULL');
    }
};
