<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `contact_items` ADD UNIQUE `contact_items_value_unique` (`value`(191))');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `contact_items` DROP INDEX `contact_items_value_unique`');
    }
};
