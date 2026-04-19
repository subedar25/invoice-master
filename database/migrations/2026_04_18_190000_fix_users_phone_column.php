<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Older migration 0001_01_01_000004 mistakenly added `last_phonename` when `phone` was missing.
 * The application expects a `phone` column on users.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'phone')) {
            return;
        }

        if (Schema::hasColumn('users', 'last_phonename')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('last_phonename', 'phone');
            });

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('last_name');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'phone')) {
            return;
        }

        if (Schema::hasColumn('users', 'last_phonename')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('phone', 'last_phonename');
        });
    }
};
