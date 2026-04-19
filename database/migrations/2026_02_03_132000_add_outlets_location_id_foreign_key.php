<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `outlets` is created in 2024_05_21; `locations` exists from 2026_02_03_131921.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('outlets') || ! Schema::hasTable('locations')) {
            return;
        }

        if (! Schema::hasColumn('outlets', 'location_id')) {
            return;
        }

        Schema::table('outlets', function (Blueprint $table) {
            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('outlets')) {
            return;
        }

        Schema::table('outlets', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
        });
    }
};
