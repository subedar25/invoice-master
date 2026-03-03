<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add description (from descriptions), status; remove parents_seasons_type.
     */
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            if (Schema::hasColumn('seasons', 'parents_seasons_type')) {
                $table->dropForeign(['parents_seasons_type']);
                $table->dropColumn('parents_seasons_type');
            }
        });

        Schema::table('seasons', function (Blueprint $table) {
            if (Schema::hasColumn('seasons', 'descriptions') && ! Schema::hasColumn('seasons', 'description')) {
                $table->renameColumn('descriptions', 'description');
            }
            if (! Schema::hasColumn('seasons', 'status')) {
                $table->boolean('status')->default(true)->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            if (Schema::hasColumn('seasons', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('seasons', 'description') && ! Schema::hasColumn('seasons', 'descriptions')) {
                $table->renameColumn('description', 'descriptions');
            }
        });

        Schema::table('seasons', function (Blueprint $table) {
            if (! Schema::hasColumn('seasons', 'parents_seasons_type')) {
                $table->unsignedBigInteger('parents_seasons_type')->nullable()->after('descriptions');
                $table->foreign('parents_seasons_type')
                    ->references('id')
                    ->on('seasons')
                    ->nullOnDelete();
            }
        });
    }
};
