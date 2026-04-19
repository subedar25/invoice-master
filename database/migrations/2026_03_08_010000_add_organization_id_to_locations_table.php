<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds organization_id after `locations` and `organizations` both exist.
 * The 2024_05_20 migration runs too early on fresh installs; this migration applies the column when possible.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('locations') || ! Schema::hasTable('organizations')) {
            return;
        }

        if (Schema::hasColumn('locations', 'organization_id')) {
            return;
        }

        Schema::table('locations', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('id')
                ->constrained('organizations')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('locations') || ! Schema::hasColumn('locations', 'organization_id')) {
            return;
        }

        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
