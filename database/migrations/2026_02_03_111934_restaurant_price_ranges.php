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
        if(Schema::hasTable('restaurant_price_ranges')) {
            return;
        }
        Schema::create('restaurant_price_ranges', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('descriptions')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_price_ranges');
    }
};
