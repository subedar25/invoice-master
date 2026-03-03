<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_types', function (Blueprint $table) {
            // This creates an 'id' column.
            // It's an unsigned BIGINT, auto-incrementing, and set as the primary key.
            $table->id();

            // This creates a 'name' column.
            // By default, string() creates a VARCHAR(255) equivalent.
            $table->string('name');

            // This is a best practice to add 'created_at' and 'updated_at' columns.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This command drops the 'client_type' table if it exists.
        Schema::dropIfExists('client_types');
    }
};