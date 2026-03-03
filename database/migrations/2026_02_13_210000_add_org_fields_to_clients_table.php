<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('year_founded', 4)->nullable();
            $table->text('hours')->nullable();
            $table->string('logo')->nullable();
            $table->json('seasons_open')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['year_founded', 'hours', 'logo', 'seasons_open']);
        });
    }
};

