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
        Schema::table('client_types', function (Blueprint $table) {
            if (! Schema::hasColumn('client_types', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (! Schema::hasColumn('client_types', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('description')->constrained('client_types')->nullOnDelete();
            }
            if (! Schema::hasColumn('client_types', 'active')) {
                $table->boolean('active')->default(true)->after('parent_id');
            }
            if (! Schema::hasColumn('client_types', 'code')) {
                $table->string('code')->nullable()->after('active');
            }
            if (! Schema::hasColumn('client_types', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_types', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['description', 'parent_id', 'active', 'code', 'deleted_at']);
        });
    }
};
