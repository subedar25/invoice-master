<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('notifications', 'organization_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->foreignId('organization_id')
                    ->nullable()
                    ->after('notifiable_id')
                    ->constrained('organizations')
                    ->nullOnDelete();
                $table->index('organization_id', 'notifications_organization_id_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('notifications', 'organization_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
                $table->dropIndex('notifications_organization_id_index');
                $table->dropColumn('organization_id');
            });
        }
    }
};
