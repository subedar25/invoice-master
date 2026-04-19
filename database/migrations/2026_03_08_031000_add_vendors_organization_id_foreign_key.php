<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `vendors` predates `organizations` (2026_03_08_000000).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vendors') || ! Schema::hasTable('organizations')) {
            return;
        }

        if (! Schema::hasColumn('vendors', 'organization_id')) {
            return;
        }

        Schema::table('vendors', function (Blueprint $table) {
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('vendors')) {
            return;
        }

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });
    }
};
