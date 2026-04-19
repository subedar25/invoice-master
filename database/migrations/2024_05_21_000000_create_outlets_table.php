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
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // FKs to locations / states / countries are added after those tables exist — see 2026_02_03_132000 and 2026_03_08_143000.
            $table->unsignedBigInteger('location_id')->nullable();
            $table->foreignId('area_manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('address')->nullable();
            $table->unsignedInteger('state_id')->nullable();
            $table->string('city')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->string('pincode', 20)->nullable();
            $table->string('photo')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outlets');
    }
};