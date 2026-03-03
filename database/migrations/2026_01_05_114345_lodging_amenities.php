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
        Schema::create('lodging_amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('descriptions')->nullable();
            $table->unsignedBigInteger('parent_amentities')->nullable();
            $table->unsignedBigInteger('image_file_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_amentities')
                  ->references('id')
                  ->on('lodging_amenities')
                  ->nullOnDelete();
                  $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lodging_amenities');
    }
};
