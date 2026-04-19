<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * On fresh databases this runs before `locations` / `organizations` exist.
     * The real change is applied in 2026_03_08_010000_add_organization_id_to_locations_table.
     */
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

    /**
     * Reverse the migrations.
     */
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