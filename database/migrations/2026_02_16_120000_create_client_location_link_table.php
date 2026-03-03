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
        Schema::create('client_location_link', function (Blueprint $table) {
            $table->id();
            // Baseline schema uses INT ids for clients/users (not BIGINT).
            $table->integer('client_id');
            // locations.id is BIGINT via $table->id() in locations migration.
            $table->unsignedBigInteger('location_id');
            $table->enum('location_type', ['physical', 'mailing', 'other'])->default('other');
            $table->integer('added_by')->nullable();
            $table->timestamp('created_date')->useCurrent();

            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('location_id')->references('id')->on('locations')->cascadeOnDelete();
            $table->foreign('added_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['client_id', 'location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_location_link');
    }
};
