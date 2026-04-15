<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'reporting_manager_id')) {
                $table->foreignId('reporting_manager_id')->nullable()->after('department_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('reporting_manager_id');
            }

            if (! Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('address');
            }

            if (! Schema::hasColumn('users', 'state')) {
                $table->string('state')->nullable()->after('city');
            }

            if (! Schema::hasColumn('users', 'pincode')) {
                $table->string('pincode', 20)->nullable()->after('state');
            }

            if (! Schema::hasColumn('users', 'photo')) {
                $table->string('photo')->nullable()->after('pincode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'reporting_manager_id')) {
                $table->dropForeign(['reporting_manager_id']);
            }

            $columns = array_filter([
                Schema::hasColumn('users', 'reporting_manager_id') ? 'reporting_manager_id' : null,
                Schema::hasColumn('users', 'address') ? 'address' : null,
                Schema::hasColumn('users', 'city') ? 'city' : null,
                Schema::hasColumn('users', 'state') ? 'state' : null,
                Schema::hasColumn('users', 'pincode') ? 'pincode' : null,
                Schema::hasColumn('users', 'photo') ? 'photo' : null,
            ]);

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
