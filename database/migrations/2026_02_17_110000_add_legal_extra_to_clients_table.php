<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'wisconsin_resale_number')) {
                $table->string('wisconsin_resale_number', 255)->nullable();
            }
            if (! Schema::hasColumn('clients', 'owner_alumni_school_district')) {
                $table->string('owner_alumni_school_district', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'wisconsin_resale_number')) {
                $table->dropColumn('wisconsin_resale_number');
            }
            if (Schema::hasColumn('clients', 'owner_alumni_school_district')) {
                $table->dropColumn('owner_alumni_school_district');
            }
        });
    }
};
