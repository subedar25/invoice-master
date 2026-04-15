<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Keep the first row for each duplicate country name, delete later duplicates.
        DB::statement('DELETE c1 FROM countries c1 INNER JOIN countries c2 WHERE c1.id > c2.id AND c1.name = c2.name');

        Schema::table('countries', function (Blueprint $table) {
            $table->unique('name', 'countries_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropUnique('countries_name_unique');
        });
    }
};
