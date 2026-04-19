<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('permissions', 'type')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('type')->default('public')->after('module_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('permissions', 'type')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
