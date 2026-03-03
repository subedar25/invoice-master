<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('client_restaurant_meal')) {
            return;
        }
        Schema::create('client_restaurant_meal', function (Blueprint $table) {
            // clients.id is INT in this project (see client_location_link, client_client_amenity).
            $table->integer('client_id');
            $table->unsignedBigInteger('restaurant_meal_id');
            $table->primary(['client_id', 'restaurant_meal_id']);
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('restaurant_meal_id')->references('id')->on('restaurant_meals')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_restaurant_meal');
    }
};
