<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::table('timesheets', function (Blueprint $table) {
        if (!Schema::hasColumn('timesheets', 'clock_in_mode')) {
            $table->enum('clock_in_mode', [
                'office',
                'remote',
                'out_of_office',
                'do_not_disturb'
            ])->nullable()->default('office')->after('start_time');
        }
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('timesheets', function (Blueprint $table) {
        if (Schema::hasColumn('timesheets', 'clock_in_mode')) {
            $table->dropColumn('clock_in_mode');
        }
    });
    }
};
