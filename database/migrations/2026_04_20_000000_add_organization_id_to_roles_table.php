<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('roles', 'organization_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropUnique(['name', 'guard_name']);
            });

            Schema::table('roles', function (Blueprint $table) {
                $table->foreignId('organization_id')
                    ->nullable()
                    ->after('guard_name')
                    ->constrained('organizations')
                    ->cascadeOnDelete();
                $table->unique(['organization_id', 'name', 'guard_name']);
            });

            $firstOrgId = DB::table('organizations')->orderBy('id')->value('id');
            if ($firstOrgId) {
                DB::table('roles')->whereNull('organization_id')->update(['organization_id' => $firstOrgId]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('roles', 'organization_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropUnique(['organization_id', 'name', 'guard_name']);
                $table->dropConstrainedForeignId('organization_id');
                $table->unique(['name', 'guard_name']);
            });
        }
    }
};
