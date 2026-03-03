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
            $table->text('description')->nullable()->after('name');
            $table->unsignedBigInteger('parent_id')->nullable()->after('description');
            $table->string('status', 50)->nullable()->default('active')->after('parent_id');
            $table->softDeletes();

            $table->foreign('parent_id')->references('id')->on('client_types')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_types', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropSoftDeletes();
            $table->dropColumn(['description', 'parent_id', 'status']);
        });
    }
};
