<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('organizations')) {
            return;
        }

        Schema::table('organizations', function (Blueprint $table) {
            if (! Schema::hasColumn('organizations', 'theme')) {
                $table->string('theme', 64)->default('dark_theam')->after('status');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('organizations')) {
            return;
        }

        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'theme')) {
                $table->dropColumn('theme');
            }
        });
    }
};
