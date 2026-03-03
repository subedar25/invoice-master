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
        Schema::create('publication_user', function (Blueprint $table) {

            $table->increments('id'); // ✅ INT UNSIGNED

            $table->integer('user_id');
            $table->unsignedInteger('publication_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('publication_id')
                ->references('id')
                ->on('publications')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['user_id', 'publication_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publication_user');
    }
};
