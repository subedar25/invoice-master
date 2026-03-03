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
         Schema::create('restaurant_meals', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('descriptions')->nullable();
            $table->unsignedBigInteger('parent_meal')->nullable();
            $table->timestamps();

            $table->foreign('parent_meal')
                  ->references('id')
                  ->on('restaurant_meals')
                  ->nullOnDelete();
                  $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_meals');
    }
};
