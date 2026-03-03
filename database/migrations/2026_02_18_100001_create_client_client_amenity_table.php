<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('client_client_amenity')) {
            return;
        }

        Schema::create('client_client_amenity', function (Blueprint $table) {
            $table->id();
            // clients.id is INT in this project (see client_location_link, client_contact).
            $table->integer('client_id');
            // client_amenities.id is INT UNSIGNED in this project; must match for FK.
            $table->unsignedInteger('client_amenity_id');
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('client_amenity_id')->references('id')->on('client_amenities')->cascadeOnDelete();
            $table->unique(['client_id', 'client_amenity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_client_amenity');
    }
};
