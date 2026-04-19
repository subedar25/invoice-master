<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * vendor_categories is created in 2024_05_20 before `organizations` exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vendor_categories') || ! Schema::hasTable('organizations')) {
            return;
        }

        if (Schema::hasColumn('vendor_categories', 'organization_id')) {
            return;
        }

        Schema::table('vendor_categories', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('id')
                ->constrained('organizations')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('vendor_categories') || ! Schema::hasColumn('vendor_categories', 'organization_id')) {
            return;
        }

        Schema::table('vendor_categories', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
