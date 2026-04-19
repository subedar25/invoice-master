<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `countries` and `states` are created on 2026_03_08; outlets predates them.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('outlets')) {
            return;
        }

        $hasCountries = Schema::hasTable('countries');
        $hasStates = Schema::hasTable('states');

        Schema::table('outlets', function (Blueprint $table) use ($hasCountries, $hasStates) {
            if ($hasCountries && Schema::hasColumn('outlets', 'country_id')) {
                $table->foreign('country_id')
                    ->references('id')
                    ->on('countries')
                    ->onDelete('set null');
            }
            if ($hasStates && Schema::hasColumn('outlets', 'state_id')) {
                $table->foreign('state_id')
                    ->references('id')
                    ->on('states')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('outlets')) {
            return;
        }

        Schema::table('outlets', function (Blueprint $table) {
            if (Schema::hasColumn('outlets', 'country_id')) {
                $table->dropForeign(['country_id']);
            }
        });

        Schema::table('outlets', function (Blueprint $table) {
            if (Schema::hasColumn('outlets', 'state_id')) {
                $table->dropForeign(['state_id']);
            }
        });
    }
};
