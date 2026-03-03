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
        if (Schema::hasTable('client_client_type')) {
            return;
        }

        Schema::create('client_client_type', function (Blueprint $table) {
            $table->id();
            // clients.id is INT in this project (see client_location_link, client_client_amenity).
            $table->integer('client_id');
            $table->unsignedBigInteger('client_type_id');
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('client_type_id')->references('id')->on('client_types')->cascadeOnDelete();
            $table->unique(['client_id', 'client_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_client_type');
    }
};
