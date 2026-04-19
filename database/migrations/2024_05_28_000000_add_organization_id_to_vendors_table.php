<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vendors')) {
            return;
        }

        if (Schema::hasColumn('vendors', 'organization_id')) {
            return;
        }

        Schema::table('vendors', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('vendors') || ! Schema::hasColumn('vendors', 'organization_id')) {
            return;
        }

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('organization_id');
        });
    }
};