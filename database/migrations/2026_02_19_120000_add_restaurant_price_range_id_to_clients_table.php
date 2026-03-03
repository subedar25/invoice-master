<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('clients', 'restaurant_price_range_id')) {
            return;
        }
        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_price_range_id')->nullable()->after('id');
            $table->foreign('restaurant_price_range_id')->references('id')->on('restaurant_price_ranges')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['restaurant_price_range_id']);
        });
    }
};
