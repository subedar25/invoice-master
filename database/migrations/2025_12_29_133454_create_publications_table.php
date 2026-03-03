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
       Schema::create('publications', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_id');
            $table->string('name');

            // Self-referencing parent
            $table->unsignedInteger('parent_id')->nullable();

            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('parent_id');

            $table->foreign('parent_id')
                ->references('id')
                ->on('publications')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
