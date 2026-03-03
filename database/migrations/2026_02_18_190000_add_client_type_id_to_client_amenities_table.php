<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('client_amenities')) {
            return;
        }

        if (Schema::hasColumn('client_amenities', 'client_type_id')) {
            return;
        }

        Schema::table('client_amenities', function (Blueprint $table) {
            $table->unsignedBigInteger('client_type_id')->nullable()->after('id');
            $table->foreign('client_type_id')->references('id')->on('client_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('client_amenities')) {
            return;
        }

        Schema::table('client_amenities', function (Blueprint $table) {
            $table->dropForeign(['client_type_id']);
        });
    }
};
