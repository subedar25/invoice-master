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
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('descriptions')->nullable();
            $table->unsignedBigInteger('parents_seasons_type')->nullable();
            $table->timestamps();

            $table->foreign('parents_seasons_type')
                  ->references('id')
                  ->on('seasons')
                  ->nullOnDelete();
             $table->softDeletes();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
