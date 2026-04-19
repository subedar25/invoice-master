<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `vendor_bank` is created in 2024_05_21; `vendors` in 2024_05_27_000000.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vendor_bank') || ! Schema::hasTable('vendors')) {
            return;
        }

        if (! Schema::hasColumn('vendor_bank', 'vendor_id')) {
            return;
        }

        Schema::table('vendor_bank', function (Blueprint $table) {
            $table->foreign('vendor_id')
                ->references('id')
                ->on('vendors')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('vendor_bank')) {
            return;
        }

        Schema::table('vendor_bank', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
        });
    }
};
