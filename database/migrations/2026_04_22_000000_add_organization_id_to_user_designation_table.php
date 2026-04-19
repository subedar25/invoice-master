<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('user_designation', 'organization_id')) {
            Schema::table('user_designation', function (Blueprint $table) {
                $table->foreignId('organization_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('organizations')
                    ->cascadeOnDelete();
            });

            $firstOrgId = DB::table('organizations')->orderBy('id')->value('id');
            if ($firstOrgId) {
                DB::table('user_designation')->whereNull('organization_id')->update(['organization_id' => $firstOrgId]);
            }

            Schema::table('user_designation', function (Blueprint $table) {
                $table->unique(['organization_id', 'name'], 'user_designation_org_name_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('user_designation', 'organization_id')) {
            Schema::table('user_designation', function (Blueprint $table) {
                $table->dropUnique('user_designation_org_name_unique');
                $table->dropConstrainedForeignId('organization_id');
            });
        }
    }
};
