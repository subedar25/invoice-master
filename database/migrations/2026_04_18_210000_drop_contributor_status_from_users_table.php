<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'contributor_status')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('contributor_status');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'contributor_status')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('contributor_status')->nullable();
        });
    }
};
