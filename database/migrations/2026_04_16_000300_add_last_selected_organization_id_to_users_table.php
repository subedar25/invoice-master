<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'last_selected_organization_id')) {
                $table->foreignId('last_selected_organization_id')
                    ->nullable()
                    ->after('user_type')
                    ->constrained('organizations')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_selected_organization_id')) {
                $table->dropConstrainedForeignId('last_selected_organization_id');
            }
        });
    }
};
